const ENCOUNTER_CHANCE = 0.15;
const MP_TICK_MS = 1300; // intervalo de sondeo multijugador (ajustable)

class OverworldScene extends Phaser.Scene {
  constructor() {
    super('OverworldScene');
  }

  init(data) {
    this.mapKey = data.mapKey || 'overworld';
    this.startTile = this.resolveSafeStartTile(data.x, data.y);
    this.party = data.party || [];
    this.inventory = data.inventory || { pokeball: 5 };
    this.appearance = data.appearance || { gender: 'boy', skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' };
  }

  // Si la posición guardada ya no es válida en el mapa actual (fuera de
  // rango, o cayendo sobre roca/agua/árbol/etc.) — algo que puede pasar
  // si el mapa se rediseña más grande/chico entre partidas, o si algo se
  // guardó mal — se usa el punto de aparición seguro del mapa en su
  // lugar, en vez de dejar al jugador atrapado sin poder moverse.
  resolveSafeStartTile(x, y) {
    const map = MAPS[this.mapKey];
    const cols = map.layout[0].length;
    const rows = map.layout.length;
    const dentroDeRango = Number.isInteger(x) && Number.isInteger(y) && x >= 0 && y >= 0 && x < cols && y < rows;
    if (dentroDeRango && !isTileBlocked(this.mapKey, x, y)) {
      return { x, y };
    }
    return { x: map.spawn.x, y: map.spawn.y };
  }

  create() {
    this.remotePlayers = {}; // username -> { container, tileX, tileY }
    this.pendingChallengeFromMe = null;
    this.incomingChallenge = null;
    this.mpBusy = false;

    this.drawMap();

    this.player = new Player(this, this.startTile.x, this.startTile.y, TILE_SIZE, this.appearance);
    const myLabel = this.add.text(0, -TILE_SIZE * 0.75, window.BIFROST_USER.username, {
      fontFamily: 'monospace', fontSize: '9px', color: '#0f380f',
      backgroundColor: '#9bbc0f', padding: { x: 2, y: 1 },
    }).setOrigin(0.5, 1);
    this.player.container.add(myLabel);

    this.setupCamera();

    this.cursors = this.input.keyboard.createCursorKeys();
    this.wasd = this.input.keyboard.addKeys('W,A,S,D');

    this.hint = this.add.text(6, 6, 'Flechas: moverse | S: guardar | R: retar | M: menú', {
      fontFamily: 'monospace', fontSize: '10px', color: '#e6ffe6',
      backgroundColor: '#0f380f', padding: { x: 4, y: 2 },
    }).setDepth(30).setScrollFactor(0);

    this.add.text(this.scale.width - 6, 6, MAPS[this.mapKey].label, {
      fontFamily: 'monospace', fontSize: '10px', color: '#e6ffe6',
      backgroundColor: '#0f380f', padding: { x: 4, y: 2 },
    }).setOrigin(1, 0).setDepth(30).setScrollFactor(0);

    this.statusText = this.add.text(6, this.scale.height - 22, '', {
      fontFamily: 'monospace', fontSize: '11px', color: '#e6ffe6',
      backgroundColor: '#0f380f', padding: { x: 4, y: 2 },
    }).setDepth(30).setScrollFactor(0);

    this.challengePrompt = this.add.text(6, this.scale.height - 40, '', {
      fontFamily: 'monospace', fontSize: '10px', color: '#0f380f',
      backgroundColor: '#9bbc0f', padding: { x: 4, y: 2 },
    }).setDepth(30).setVisible(false).setScrollFactor(0);

    this.input.keyboard.on('keydown-S', () => this.saveGame());
    this.input.keyboard.on('keydown-R', () => this.tryChallengeNearby());
    this.input.keyboard.on('keydown-Y', () => this.respondToIncomingChallenge(true));
    this.input.keyboard.on('keydown-N', () => this.respondToIncomingChallenge(false));
    this.input.keyboard.on('keydown-M', () => this.openMenu());

    this.mpTimer = this.time.addEvent({ delay: MP_TICK_MS, loop: true, callback: () => this.multiplayerTick() });
    this.multiplayerTick();

    this.events.on('resume', () => {
      this.input.keyboard.enabled = true;
      this.statusText.setText('');
    });
    this.events.on('shutdown', () => { if (this.mpTimer) this.mpTimer.remove(); });
  }

  // Snapshot de todo lo necesario para volver a armar esta escena tal cual
  // estaba — se usa al entregarle el control a otra escena (batalla,
  // editor de apariencia) para poder reconstruirla exactamente al volver.
  captureSnapshot() {
    return {
      mapKey: this.mapKey,
      x: this.player.tileX,
      y: this.player.tileY,
      party: this.party,
      inventory: this.inventory,
      appearance: this.appearance,
    };
  }

  // Reemplaza esta escena por completo por otra (batalla, editor de
  // apariencia, etc.) en vez de pausarla y lanzar la otra encima. Dejar
  // esta escena "pausada de fondo" mientras se lanzaba otra encima
  // resultaba en pantallas que no respondían al clic (se confirmó tanto
  // con el menú como con "cambiar apariencia" — el mismo patrón fallaba
  // en los dos casos). `scene.start()` en cambio SÍ es confiable: es el
  // mismo mecanismo que ya usan los warps entre mapas (`changeMap()`),
  // que nunca han dado este problema.
  handoffTo(sceneKey, extraData) {
    this.scene.start(sceneKey, { ...extraData, returnData: this.captureSnapshot() });
  }

  openMenu() {
    window.BIFROST_OPEN_MENU(this);
  }

  // ---------- Mapa ----------

  drawMap() {
    const layout = MAPS[this.mapKey].layout;
    for (let y = 0; y < layout.length; y++) {
      for (let x = 0; x < layout[y].length; x++) {
        drawTile(this, x, y, TILE_SIZE, layout[y][x]);
      }
    }
    // El "gran árbol" se detecta como bloque conectado de tile 5 y se
    // dibuja como una sola copa grande, no como celdas repetidas.
    findTile5Regions(layout).forEach((region) => drawBigTree(this, region, TILE_SIZE));
    this.drawLandmarks();
  }

  // Hitos narrativos propios de cada mapa (ej. "Cueva de Don Emilio" en
  // Renca): una pequeña etiqueta flotando en el mundo, visible al
  // acercarse. Puramente decorativo por ahora — no hace nada al tocarlo.
  drawLandmarks() {
    const landmarks = MAPS[this.mapKey].landmarks || [];
    landmarks.forEach(({ x, y, label }) => {
      const worldX = x * TILE_SIZE + TILE_SIZE / 2;
      const worldY = y * TILE_SIZE - TILE_SIZE * 0.4;
      this.add.text(worldX, worldY, label, {
        fontFamily: 'monospace', fontSize: '9px', color: '#e6ffe6',
        backgroundColor: '#0f380f', padding: { x: 4, y: 2 },
      }).setOrigin(0.5, 1).setDepth(5);
    });
  }

  // El mapa puede ser más grande que el canvas visible: la cámara sigue al
  // jugador y no se sale de los límites reales del mapa actual.
  setupCamera() {
    const layout = MAPS[this.mapKey].layout;
    const mapPixelWidth = layout[0].length * TILE_SIZE;
    const mapPixelHeight = layout.length * TILE_SIZE;
    this.cameras.main.setBounds(0, 0, mapPixelWidth, mapPixelHeight);
    this.cameras.main.startFollow(this.player.container, true, 1, 1);
  }

  isBlocked(x, y) {
    return isTileBlocked(this.mapKey, x, y);
  }

  update() {
    if (!this.player.canMove()) return;

    let dx = 0;
    let dy = 0;
    if (this.cursors.left.isDown || this.wasd.A.isDown) dx = -1;
    else if (this.cursors.right.isDown || this.wasd.D.isDown) dx = 1;
    else if (this.cursors.up.isDown || this.wasd.W.isDown) dy = -1;
    else if (this.cursors.down.isDown || this.wasd.S.isDown) dy = 1;

    if (dx !== 0 || dy !== 0) {
      this.player.move(dx, dy, this.isBlocked.bind(this), (tx, ty) => this.onArrive(tx, ty));
    }
  }

  onArrive(tileX, tileY) {
    const warp = warpAt(this.mapKey, tileX, tileY);
    if (warp) {
      this.changeMap(warp.to, warp.spawn);
      return;
    }
    const tile = tileAt(this.mapKey, tileX, tileY);
    if (tile === 1 && Math.random() < ENCOUNTER_CHANCE) {
      this.startEncounter();
    }
  }

  changeMap(mapKey, spawn) {
    this.scene.restart({
      mapKey,
      x: spawn.x,
      y: spawn.y,
      party: this.party,
      inventory: this.inventory,
      appearance: this.appearance,
    });
  }

  // ---------- Batalla contra criaturas salvajes ----------

  startEncounter() {
    // Sin compañeros todavía no hay con qué pelear — las criaturas se
    // domesticarán más adelante (sistema pendiente). Por ahora solo se
    // avisa que hay algo en la hierba, sin iniciar batalla.
    if (this.party.length === 0) {
      this.statusText.setText('Algo se movió en la hierba... pero no tienes compañeros todavía.');
      return;
    }
    const enemy = makeMonsterInstance(randomSpeciesKey());
    this.handoffTo('BattleScene', { playerMonster: this.party[0], enemyMonster: enemy });
  }

  // ---------- Guardado ----------

  async saveGame() {
    this.statusText.setText('Guardando...');
    try {
      const res = await fetch('api/save_game.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          mapKey: this.mapKey,
          x: this.player.tileX,
          y: this.player.tileY,
          party: this.party,
          inventory: this.inventory,
        }),
      });
      if (!res.ok) throw new Error();
      this.statusText.setText('Partida guardada.');
    } catch (err) {
      this.statusText.setText('No se pudo guardar (¿backend corriendo?).');
    }
  }

  // ---------- Multijugador ----------

  async multiplayerTick() {
    if (this.mpBusy) return; // no solapar peticiones si la red va lenta
    this.mpBusy = true;
    try {
      await Promise.all([this.broadcastPosition(), this.fetchNearbyPlayers(), this.pollChallenges()]);
    } catch (err) {
      // Sin red o backend caído: el juego sigue jugable en modo local.
    }
    this.mpBusy = false;
  }

  async broadcastPosition() {
    await fetch('api/update_position.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({
        mapKey: this.mapKey,
        x: this.player.tileX,
        y: this.player.tileY,
        facing: this.player.facing,
      }),
    });
  }

  async fetchNearbyPlayers() {
    const res = await fetch(`api/nearby_players.php?map=${encodeURIComponent(this.mapKey)}`, { credentials: 'include' });
    if (!res.ok) return;
    const data = await res.json();
    this.updateRemotePlayers(data.players || []);
  }

  updateRemotePlayers(players) {
    if (!this.sys.isActive()) return;
    const seen = new Set();

    for (const p of players) {
      seen.add(p.username);
      const worldX = p.x * TILE_SIZE + TILE_SIZE / 2;
      const worldY = p.y * TILE_SIZE + TILE_SIZE / 2;
      let remote = this.remotePlayers[p.username];

      if (!remote) {
        remote = this.createRemotePlayer(p.username, p.appearance, p.facing);
        remote.container.setPosition(worldX, worldY);
        this.remotePlayers[p.username] = remote;
      } else {
        if (remote.tileX !== p.x || remote.tileY !== p.y) {
          this.tweens.add({ targets: remote.container, x: worldX, y: worldY, duration: 350 });
        }
        if (remote.facing !== p.facing) {
          applyFacingToVisual(remote.visual, p.facing);
          remote.facing = p.facing;
        }
      }
      remote.tileX = p.x;
      remote.tileY = p.y;
    }

    Object.keys(this.remotePlayers).forEach((username) => {
      if (!seen.has(username)) {
        this.remotePlayers[username].container.destroy();
        delete this.remotePlayers[username];
      }
    });
  }

  createRemotePlayer(username, appearance, facing) {
    const visual = buildCharacterVisual(this, TILE_SIZE, appearance || { gender: 'boy', skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' });
    applyFacingToVisual(visual, facing || 'down');
    const label = this.add.text(0, -TILE_SIZE * 0.75, username, {
      fontFamily: 'monospace', fontSize: '9px', color: '#0f380f',
      backgroundColor: '#9bbc0f', padding: { x: 2, y: 1 },
    }).setOrigin(0.5, 1);
    const container = this.add.container(0, 0, [visual, label]);
    container.setDepth(9);
    return { container, visual, tileX: 0, tileY: 0, facing: facing || 'down' };
  }

  findAdjacentUsername() {
    const px = this.player.tileX;
    const py = this.player.tileY;
    for (const [username, remote] of Object.entries(this.remotePlayers)) {
      const dist = Math.max(Math.abs(remote.tileX - px), Math.abs(remote.tileY - py));
      if (dist <= 1) return username;
    }
    return null;
  }

  async tryChallengeNearby() {
    if (this.pendingChallengeFromMe) return;
    const target = this.findAdjacentUsername();
    if (!target) {
      this.statusText.setText('No hay ningún jugador junto a ti.');
      return;
    }
    this.pendingChallengeFromMe = target;
    this.statusText.setText(`Reto enviado a ${target}, esperando respuesta...`);
    try {
      const res = await fetch('api/challenge_send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ toUsername: target }),
      });
      const data = await res.json();
      if (!res.ok) {
        this.statusText.setText(data.error || 'No se pudo enviar el reto.');
        this.pendingChallengeFromMe = null;
      }
    } catch (err) {
      this.statusText.setText('No se pudo enviar el reto (¿backend corriendo?).');
      this.pendingChallengeFromMe = null;
    }
  }

  async pollChallenges() {
    const res = await fetch('api/challenge_poll.php', { credentials: 'include' });
    if (!res.ok) return;
    const data = await res.json();
    if (!this.sys.isActive()) return;

    if (data.incoming && (!this.incomingChallenge || this.incomingChallenge.challengeId !== data.incoming.challengeId)) {
      this.incomingChallenge = data.incoming;
      this.challengePrompt.setText(`${data.incoming.fromUsername} te retó a batalla. [Y] Aceptar  [N] Rechazar`);
      this.challengePrompt.setVisible(true);
    } else if (!data.incoming && this.incomingChallenge) {
      this.incomingChallenge = null;
      this.challengePrompt.setVisible(false);
    }

    if (data.declined && this.pendingChallengeFromMe) {
      this.statusText.setText(`${this.pendingChallengeFromMe} rechazó tu reto.`);
      this.pendingChallengeFromMe = null;
    }

    if (data.acceptedBattleId) {
      this.enterPvpBattle(data.acceptedBattleId);
    }
  }

  async respondToIncomingChallenge(accept) {
    if (!this.incomingChallenge) return;
    const challengeId = this.incomingChallenge.challengeId;
    const fromUsername = this.incomingChallenge.fromUsername;
    this.challengePrompt.setVisible(false);
    this.incomingChallenge = null;
    try {
      const res = await fetch('api/challenge_respond.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ challengeId, accept }),
      });
      const data = await res.json();
      if (data.ok && data.accepted && data.battleId) {
        this.enterPvpBattle(data.battleId);
      } else if (!accept) {
        this.statusText.setText(`Rechazaste el reto de ${fromUsername}.`);
      }
    } catch (err) {
      this.statusText.setText('No se pudo responder al reto.');
    }
  }

  enterPvpBattle(battleId) {
    this.pendingChallengeFromMe = null;
    this.handoffTo('PvpBattleScene', { battleId });
  }
}
