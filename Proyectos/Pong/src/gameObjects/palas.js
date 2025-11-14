class Palas extends Phaser.GameObjects.Sprite {
    constructor(scene, x, y, type) {
        super(scene, x, y, type);
        scene.add.existing(this);

        //agregamos physics a las palas
        scene.add.existing(this);
        scene.physics.world.enable(this);
        this.body.setImmovable(true);
        
    }
}

export default Palas;