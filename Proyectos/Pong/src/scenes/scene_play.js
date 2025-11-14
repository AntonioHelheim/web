import Palas from "../gameObjects/palas.js";

class Scene_play extends Phaser.Scene { 
    constructor() {
        super({ key: 'Scene_play' });
    }

    // preload() {
    // console.log("se ha cargado la escena Scene_play");
    // }

    create() {
        let center_width = this.sys.game.config.width / 2;
        let center_height = this.sys.game.config.height / 2;


        //Separator
        this.add.image(center_width, center_height, 'separador');
        //console.log(this);


        //Palas
        // this.izquierda = this.physics.add.image(30, center_height, 'izquierda');
        this.izquierda = new Palas(this, 30, center_height, 'izquierda');
        // this.derecha = this.physics.add.image(this.sys.game.config.width - 30, center_height, 'derecha');
        this.derecha = new Palas(this, this.sys.game.config.width - 30, center_height, 'derecha');


        //Bola
        //Ponemos fronteras activas en los margenees
        this.physics.world.setBoundsCollision(false, false, true, true);
        //Creamos la bola en el centro
        this.bola = this.physics.add.image(center_width, center_height, 'ball');
        //Le diremos que rebote
        this.bola.setCollideWorldBounds(true);
        this.bola.setBounce(1);
        //Ajustamos la velocidad de la bola
        this.bola.setVelocityX(-200);


        //fisicas para que la bola choque con las palas
        this.physics.add.collider(this.bola, this.izquierda,this.chocaPala, null, this);
        this.physics.add.collider(this.bola, this.derecha,this.chocaPala, null, this);
    
    
    }

    chocaPala() {

    }

}   

export default Scene_play;   