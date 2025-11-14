class Bootloader extends Phaser.Scene {
    constructor() {
        super({ key: 'Bootloader' });
    }

    preload() {
        // console.log("se ha cargado la escena Bootloader");
        this.load.on('complete', () => {
            this.scene.start('Scene_play');

        });

        
        this.load.image('ball', './assets/ball.png');
        this.load.image('izquierda', './assets/left_pallete.png');
        this.load.image('derecha', './assets/right_pallete.png');
        this.load.image('separador', './assets/separator.png');
        
    }

    // create() {
    // this.add.image(100,100, 'derecha');
    // }

}   

export default Bootloader;