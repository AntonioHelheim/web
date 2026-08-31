// ⚠️ EN PAUSA: ya no está registrada en main.js/game.php. El menú se
// reconstruyó como overlay HTML normal (ver game.php + assets/style.css,
// clase .gb-menu-overlay) porque esta versión con escena de Phaser daba
// problemas persistentes (se abría y cerraba sola, y luego el juego se
// quedaba congelado al abrirla) que no se lograron resolver de forma
// confiable dentro del propio Phaser. Se deja el archivo como referencia.
//
// Menú simple de pausa, accesible con la tecla M desde el mapa. Se lanza
// sobre OverworldScene (pausada), igual que las batallas.
class MenuScene extends Phaser.Scene {
  constructor() {
    super('MenuScene');
  }

  init(data) {
    this.overworldScene = data.overworldScene;
  }

  create() {
    console.log('[Bifrost] MenuScene.create() iniciando...');
    try {
      const w = this.scale.width;
      const h = this.scale.height;
      this.actionTaken = false; // evita doble-clic (ej. dos veces en "Cambiar apariencia")

      this.add.rectangle(w / 2, h / 2, w, h, 0x000000).setAlpha(0.55).setDepth(0);
      this.add.rectangle(w / 2, h / 2, w * 0.7, h * 0.55, GB_PALETTE.dark)
        .setStrokeStyle(3, GB_PALETTE.lightest).setDepth(1);
      this.add.text(w / 2, h * 0.28, 'Menú', {
        fontFamily: 'monospace', fontSize: '13px', color: '#e6ffe6',
      }).setOrigin(0.5).setDepth(2);

      this.makeButton(w / 2, h * 0.46, 'Cambiar apariencia', () => this.openAppearanceEditor());
      this.makeButton(w / 2, h * 0.6, 'Volver al juego', () => this.closeMenu());

      // A propósito NO hay atajo de teclado con M para cerrar este menú.
      // Aunque se retrasara su registro, si el jugador mantiene la tecla
      // presionada aunque sea un instante, el navegador puede mandar un
      // segundo evento de "tecla repetida" mientras el listener ya está
      // activo, cerrando el menú solo. El botón de abajo usa clic (un
      // evento distinto a la tecla M que abrió esta pantalla), sin ese riesgo.
      console.log('[Bifrost] MenuScene.create() terminó sin errores — el menú debería verse ahora.');
    } catch (err) {
      console.error('[Bifrost] ERROR dentro de MenuScene.create():', err);
      // No dejar el juego congelado: intenta volver al mapa igual.
      this.scene.stop();
      this.scene.resume('OverworldScene');
    }
  }

  makeButton(x, y, label, onClick) {
    const btn = this.add.rectangle(x, y, 180, 26, GB_PALETTE.lightest)
      .setStrokeStyle(2, GB_PALETTE.darkest)
      .setInteractive({ useHandCursor: true })
      .setDepth(2);
    const txt = this.add.text(x, y, label, {
      fontFamily: 'monospace', fontSize: '10px', color: '#0f380f',
    }).setOrigin(0.5).setDepth(3);
    btn.on('pointerdown', onClick);
    return [btn, txt];
  }

  openAppearanceEditor() {
    console.log('[Bifrost] Clic en "Cambiar apariencia"');
    if (this.actionTaken) return;
    this.actionTaken = true;
    this.scene.stop();
    this.scene.launch('CharacterCreationScene', {
      editMode: true,
      appearance: this.overworldScene.appearance,
    });
  }

  closeMenu() {
    console.log('[Bifrost] Clic en "Volver al juego"');
    if (this.actionTaken) return;
    this.actionTaken = true;
    this.scene.stop();
    this.scene.resume('OverworldScene');
  }
}
