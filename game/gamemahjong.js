//https://codepen.io/lewster32/pen/gOYvWZO

"use strict";

class Tile extends Phaser.GameObjects.Sprite {
    static get OK_TINT() {
        return 0x99ff99;
    }
    static get INVALID_TINT() {
        return 0xff9999;
    }
    static get SIZE() {
        return new Phaser.Geom.Rectangle(0, 8, 58, 82);
    }
    static get Z_HEIGHT() {
        return 6;
    }
    static get DOTS() {
        return [11, 12, 13, 14, 16, 17, 18, 19, 20];
    }
    static get BAMBOO() {
        return [0, 1, 2, 3, 4, 5, 6, 8, 9];
    }
    static get CHARACTERS() {
        return [7, 15, 23, 27, 28, 29, 30, 32, 33];
    }
    static get WINDS() {
        return [35, 36, 37, 38];
    }
    static get DRAGONS() {
        return [26, 34, 41];
    }
    static get FLOWERS() {
        return [10, 22, 24, 25];
    }
    static get SEASONS() {
        return [21, 31, 39, 40];
    }

    constructor(scene, board, x, y, z, index) {
        super(
            scene,
            x * Tile.SIZE.width + Tile.Z_HEIGHT * z,
            y * Tile.SIZE.height - Tile.Z_HEIGHT * z,
            "tiles",
            index || 0
        );

        this.gridPos = new Phaser.Geom.Point(x, y);
        this.z = z;

        this.setDisplayOrigin(0, 0);

        this.setInteractive();
        this.on("pointerdown", this.clickMe, this);

        this.board = board;

        this.scene.add.existing(this);

        this.neighbours = {
            top: [],
            bottom: [],
            left: [],
            right: [],
            above: []
        };
    }

    isBlocked() {
        return (
            this.neighbours.above.length ||
            (this.neighbours.left.length && this.neighbours.right.length)
        );
    }

    destroy() {
        const direction = this.neighbours.left.length ? 1 : -1;
        const tween = this.scene.tweens.add({
            targets: this,
            x: this.x + 200 * direction,
            rotation: -0.66 + Math.random() * 0.66,
            alpha: 0,
            ease: "Circ.easeIn",
            duration: 250,
            onUpdate: () => {
                this.board.updateShadows();
            },
            onComplete: () => {
                super.destroy();
                if (this.board) {
                    this.board.updateTileNeighbours();
                    this.board.updateShadows();
                }
            }
        });
    }

    isMatch(otherTile) {
        return (
            otherTile.active &&
            otherTile !== this &&
            (otherTile.frame.name === this.frame.name ||
                (Tile.FLOWERS.indexOf(otherTile.frame.name) !== -1 &&
                    Tile.FLOWERS.indexOf(this.frame.name) !== -1) ||
                (Tile.SEASONS.indexOf(otherTile.frame.name) !== -1 &&
                    Tile.SEASONS.indexOf(this.frame.name) !== -1))
        );
    }

    clickMe() {
        if (this.scene.debug) {
            Object.values(this.neighbours).forEach(direction => {
                if (direction && direction.length) {
                    direction.forEach(tile => {
                        tile.setTint(0xaaaaff);
                        setTimeout(() => {
                            tile.clearTint();
                        }, 500);
                    });
                }
            });
        }
        // Deselecting currently selected tile
        if (this.scene.selectedTile === this) {
            this.clearTint();
            this.scene.deselect();
        } else if (this.isBlocked()) {
            // Can't select this tile (it's blocked)
            this.setTint(Tile.INVALID_TINT);
            setTimeout(() => {
                this.clearTint();
            }, 500);
            return;
        } else if (!this.scene.selectedTile) {
            // Select this tile
            this.scene.select(this);

            if (this.scene.debug) {
                this.board
                    .getTiles()
                    .filter(tile => {
                        return this.isMatch(tile) && !tile.isBlocked();
                    })
                    .forEach(tile => {
                        tile.setTint(0xffff88);
                        setTimeout(() => {
                            tile.clearTint();
                        }, 2000);
                    });
            }
        } else if (this.isMatch(this.scene.selectedTile)) {
            // Check for a match with the selected tile
            this.scene.selectedTile.destroy();
            this.scene.deselect();
            this.destroy();
        } else {
            // No match, deselect both tiles
            this.setTint(Tile.INVALID_TINT);
            this.scene.selectedTile.setTint(Tile.INVALID_TINT);
            setTimeout(() => {
                this.clearTint();
                this.scene.selectedTile.clearTint();
                this.scene.deselect();
            }, 500);
        }
    }
}

class Board extends Phaser.GameObjects.Container {
    constructor(scene, x, y, layout) {
        super(scene, x, y);
        this.scene.add.existing(this);

        this.layout = layout;
        this.layout.board = this;

        this.layers = [];
        let shadow, container;
        for (let z = 0; z < 12; z += 1) {
            if (z % 2 === 0) {
                shadow = this.scene.add.renderTexture(-7, 7, 1024, 768);
                shadow.depth = z;
                shadow.setTint(0x330000);
                shadow.alpha = 0;
                this.add(shadow);
            } else {
                container = this.scene.add.container(0, 0);
                container.depth = z;
                container.shadow = shadow;
                this.add(container);
                this.layers.push(container);
            }
        }

        this.tiles = [];
    }

    getTiles() {
        let tiles = [];
        this.layers.forEach(layer => {
            tiles = tiles.concat(layer.list.filter(tile => tile.active));
        });
        return tiles;
    }

    getAllTiles() {
        let tiles = [];
        this.layers.forEach(layer => {
            tiles = tiles.concat(layer.list);
        });
        return tiles;
    }

    shuffle() {
        const tiles = this.getTiles();
        const tileFrames = Phaser.Utils.Array.Shuffle(tiles.map(tile => tile.frame.name));
        tiles.forEach(tile => {
            tile.setFrame(tileFrames.pop());
        });
        this.updateTileNeighbours();
    }

    arrange() {
        this.list.filter(child => child instanceof Tile).forEach(tile => {
            this.layers[tile.z].add(tile);
            this.layers[tile.z].list.sort((a, b) => b.x - a.x + a.y - b.y);
        });

        this.getTiles().forEach(tile => {
            tile.x += 32;
            tile.y -= 32;
            tile.alpha = 0;
            tile.disableInteractive();
        });

        this.scene.tweens.add({
            targets: this.getTiles(),
            x: "-=32",
            y: "+=32",
            alpha: 1,
            duration: 1000,
            ease: "Bounce.easeOut",
            delay: this.scene.tweens.stagger(10),
            onComplete: () => {
                this.getTiles().forEach(tile => {
                    tile.setInteractive();
                });

                this.updateShadows();

                document.querySelector('html').classList.add('lit');
                this.scene.tweens.add({
                    targets: this.layers.map(layer => layer.shadow),
                    alpha: .4,
                    duration: 500,
                    onComplete: () => {
                        document.querySelector('html').classList.add('after-lit');
                    }
                });
            }
        });
    }

    updateShadows() {
        this.layers.forEach(layer => {
            layer.shadow.clear();
            layer.shadow.draw(layer);
        });
    }

    // Note that there's no grid snapping; tiles can be placed anywhere and this method
    // will have a good go at trying to find adjacent tiles, though it works best if
    // the tiles are 'touching' like they would in real life.
    getTilesNear(tile, direction) {
        let dx = tile.gridPos.x,
            dy = tile.gridPos.y,
            dz = tile.z;
        switch (direction) {
            case "left":
                dx -= 1;
                break;
            case "right":
                dx += 1;
                break;
            case "top":
                dy -= 1;
                break;
            case "bottom":
                dy += 1;
                break;
            case "above":
                dz += 1;
                break;
        }

        const filtered = this.getTiles().filter(otherTile => {
            return (
                tile != otherTile &&
                otherTile.active &&
                otherTile.z === dz &&
                // Bog standard AABB method
                otherTile.gridPos.x > dx - 1 &&
                otherTile.gridPos.x < dx + 1 &&
                otherTile.gridPos.y > dy - 1 &&
                otherTile.gridPos.y < dy + 1
            );
        });
        if (filtered && filtered.length) {
            return filtered;
        }
        return [];
    }

    // In happier times this would be efficient and only update tiles which have been
    // affected by the previous move, but it's fast enough to just check them all...
    updateTileNeighbours() {
        this.getTiles().forEach(tile => {
            tile.clearTint();
            tile.neighbours.left = this.getTilesNear(tile, "left");
            tile.neighbours.right = this.getTilesNear(tile, "right");
            tile.neighbours.top = this.getTilesNear(tile, "top");
            tile.neighbours.bottom = this.getTilesNear(tile, "bottom");
            tile.neighbours.above = this.getTilesNear(tile, "above");
        });

        let remainingCount = this.getRemainingCount();
        if (this.scene.debug && this.getTiles().length%2 == 0) {
            saveGame(this.gameId, this.getGameStaus());
        }
        // GAME OVER YEAHHHHH
        if (remainingCount === 0 && this.getTiles().length%2 == 0) {
            if (this.getTiles().length>0) {
                this.getTiles().forEach(tile => {
                    tile.setTint(0x997755);
                    tile.removeInteractive();
                });
                alert("Sorry no free tiles left!");
            } else if (this.getTiles().length==0) {
                alert("You won!")
            }
        }
        else {
            this.getTiles().forEach(tile => {
                tile.clearTint();
                tile.setInteractive();
            });
        }
    }

    getRemainingCount() {
        let remaining = this.getTiles().filter(tile => !tile.isBlocked());
        let remainingCount = 0;
        remaining.forEach(tile1 => {
            const matching = remaining.filter(tile2 => {
                return tile1.isMatch(tile2);
            });
            if (matching && matching.length) {
                if (this.scene.debug) {
                    matching.forEach(tile => {
                        tile.setTint(0xffaaff);
                    });
                }
                remainingCount += matching.length;
            }
        });
        return remainingCount/2;
    }

    getGameStaus() {
        let ret = new Object();
        ret.gameId = this.gameId;
        ret.remainingCount=this.getRemainingCount();
        ret.remainingTiles=this.getTiles().length%2;
        ret.layout = this.layout.board.name;
        ret.tiles = [];
        this.getAllTiles().forEach(tile => {
            let t = new Object();
            t.active = tile.active;
            t.name = tile.frame.name;
            ret.tiles.push(t);
        });
        return ret;
    }
}

class BoardLayoutPosition {
    constructor(x, y, z) {
        this.x = x;
        this.y = y;
        this.z = z;
    }

    get identity() {
        return `${this.x}:${this.y}:${this.z}`;
    }
}

class BoardLayout {
    static get LAYOUTS() {
        return {
            test: layout => {
                layout.createColumn(1, 0, 4, 0);
                layout.createColumn(5, 0, 4, 0);
                layout.createColumn(9, 0, 4, 0);
                layout.createTile(1,1.5, 1);
                layout.createTile(5,2.5, 1);
                layout.createTile(9,3.5, 1);
            },
            turtle: layout => {
                layout.createRow(1, 12, 0, 0);
                layout.createRow(3, 10, 1, 0);
                layout.createRow(2, 11, 2, 0);
                layout.createRect(1, 3, 12, 4, 0);
                layout.createRow(2, 11, 5, 0);
                layout.createRow(3, 10, 6, 0);
                layout.createRow(1, 12, 7, 0);
                layout.createTile(0, 3.5, 0);
                layout.createRow(13, 14, 3.5, 0);
                layout.createRect(4, 1, 9, 6, 1);
                layout.createRect(5, 2, 8, 5, 2);
                layout.createRect(6, 3, 7, 4, 3);
                layout.createTile(6.5, 3.5, 4);
            },
            castle: layout => {
                layout.createCube(1, 0, 9, 8, 0, 3);
                layout.createCube(2, 1, 8, 7, 1, 4, true);
                layout.createRowStack(0, 10, 3, 0, 4);
                layout.createRowStack(0, 10, 5, 0, 4);
                layout.createCube(3, 3, 7, 5, 2, 4, true);
                layout.createRow(2.5, 3.5, 1.5, 1);
                layout.createTile(3, 1.5, 2);
                layout.createRow(6.5, 7.5, 6.5, 1);
                layout.createTile(7, 6.5, 2);
                layout.createTile(1, 0, 4);
                layout.createTile(9, 0, 4);
                layout.createTile(1, 8, 4);
                layout.createTile(9, 8, 4);
            },
            castleh: layout => {
                layout.createCube(1, 1, 12, 7, 0, 3);
                layout.createCube(2, 2, 11, 6, 1, 4, true);
                layout.createRowStack(0, 13, 4, 0, 4);
                layout.createColumnStack(5, 1, 7, 0, 4);
                layout.createColumnStack(8, 1, 7, 0, 4);
                layout.createCube(3, 3, 9, 5, 2, 4, true);
                layout.createRow(2.5, 3.5, 2.5, 1);
                layout.createTile(3, 2.5, 2);
                layout.createRow(9.5, 10.5, 5.5, 1);
                layout.createTile(10, 5.5, 2);
            },
            duncher: layout => {
                layout.createRect(2, 1, 8, 3, 0);
                layout.createRect(3, 1, 7, 3, 1);
                layout.createRect(1, 3, 9, 4, 0);
                layout.createRect(2, 3, 8, 4, 1);
                layout.createCube(0, 4, 12, 6, 0, 2);
                layout.createTile(0, 4, 2, true);
                layout.createTile(8, 1, 0, true);
                layout.createTile(12, 4, 2, true);
                layout.createRow(2, 4, 7, 0);
                layout.createRow(7, 9, 7, 0);
                layout.createRow(0, 12, 6, 2, true);
                layout.createRow(0, 12, 1, 1, true);
                layout.createTile(3, 8, 0);
                layout.createTile(8, 8, 0);
            }
        };
    }

    constructor(layout) {
        this.positions = [];

        this.frames = [];
        if (layout===undefined || layout=="test") {
            this.frames = this.frames.concat(Tile.DOTS);
        } else {
            // Create 4x each of the ordinary tiles
            for (let i = 0; i < 4; i++) {
                this.frames = this.frames.concat(Tile.DOTS, Tile.BAMBOO, Tile.CHARACTERS, Tile.WINDS, Tile.DRAGONS);
            }
            // Create 1x each of the bonus (season and flower) tiles
            this.frames = this.frames.concat(Tile.FLOWERS, Tile.SEASONS);
        }
        // Shuffle the array
        this.frames = Phaser.Utils.Array.Shuffle(this.frames);
    }

    fillPositions(tiles) {
        let frameIndex = 0;
        if (tiles === undefined || tiles === null || tiles.length ==0) {
            this.positions.forEach(pos => {
                this.board.add(
                    new Tile(
                        this.board.scene,
                        this.board,
                        pos.x,
                        pos.y,
                        pos.z,
                        this.frames[frameIndex++ % this.frames.length]
                    )
                );
            });
        } else {
            this.positions.forEach(pos => {
                if  (tiles[frameIndex].active) {
                    this.board.add(
                        new Tile(
                            this.board.scene,
                            this.board,
                            pos.x,
                            pos.y,
                            pos.z,
                            tiles[frameIndex].name
                        )
                    );
                }
                frameIndex++;
            });
        }
    }

    createRow(x1, x2, y, z, carve) {
        for (let x = x1; x <= x2; x++) {
            this.createTile(x, y, z, carve);
        }
    }

    createRowStack(x1, x2, y, z1, z2, carve) {
        for (let z = z1; z <= z2; z++) {
            this.createRow(x1, x2, y, z, carve);
        }
    }

    createColumn(x, y1, y2, z, carve) {
        for (let y = y1; y <= y2; y++) {
            this.createTile(x, y, z, carve);
        }
    }

    createColumnStack(x, y1, y2, z1, z2, carve) {
        for (let z = z1; z <= z2; z++) {
            this.createColumn(x, y1, y2, z, carve);
        }
    }

    createStack(x, y, z1, z2, carve) {
        for (let z = z1; z <= z2; z++) {
            this.createTile(x, y, z, carve);
        }
    }

    createRect(x1, y1, x2, y2, z, carve) {
        for (let y = y1; y <= y2; y++) {
            for (let x = x1; x <= x2; x++) {
                this.createTile(x, y, z, carve);
            }
        }
    }

    createCube(x1, y1, x2, y2, z1, z2, carve) {
        for (let z = z1; z <= z2; z++) {
            this.createRect(x1, y1, x2, y2, z, carve);
        }
    }

    createTile(x, y, z, carve) {
        const newPos = new BoardLayoutPosition(x, y, z);
        if (carve) {
            this.positions = this.positions.filter(
                pos => pos.identity !== newPos.identity
            );
            return;
        }
        // Prevent overlapping positions
        if (this.positions.filter(pos => pos.identity === newPos.identity).length) {
            return;
        }
        this.positions.push(new BoardLayoutPosition(x, y, z));
    }

    loadPreset(gameStatus) {
        if (BoardLayout.LAYOUTS.hasOwnProperty(gameStatus.layout)) {
            console.log("Loading layout:", gameStatus.layout);
            BoardLayout.LAYOUTS[gameStatus.layout](this);
            console.log("Total positions:", this.positions.length," Frames:",this.frames.length);
        }
        this.fillPositions(gameStatus.tiles);
    }
}

class Main extends Phaser.Scene {
    constructor(gameId,gameStatus) {
        super({ key: "main" });
        this.gameStatus=gameStatus;
        this.gameId=gameId;
    }
    create() {
        this.debug = true; // turn this on if you're a nasty rotten cheat
        const board = new Board(this, 72, 72, new BoardLayout());

        //test,turtle, castle,castleh, duncher
        board.layout.loadPreset(this.gameStatus);
        board.name = this.gameStatus.layout;


        board.arrange();
        board.updateTileNeighbours();

        this.selectedTile = null;

        //Select the first tile
        this.select = tile => {
            this.selectedTile = tile;
            tile.setTint(Tile.OK_TINT);
            this.tweens.add({
                targets: tile,
                x: "+=1",
                y: "-=1",
                duration: 100,
                ease: "Circle.easeInOut"
            });
        };

        this.deselect = () => {
            this.tweens.add({
                targets: this.selectedTile,
                x: "-=1",
                y: "+=1",
                duration: 100,
                ease: "Circle.easeInOut"
            });
            this.selectedTile = null;
        };

        const shuffleKey = this.input.keyboard.addKey('s');
        shuffleKey.on('up', () => {
            board.shuffle();
            console.log("Shuffling board...");
        });
    }
}

class Preloader extends Phaser.Scene {
    constructor() {
        super({ key: "preloader" });
    }
    create() {
        const sheetImage = new Image();
        sheetImage.onload = () => {
            this.textures.addSpriteSheet("tiles", sheetImage, {
                frameWidth: 64,
                frameHeight: 89,
                spacing: 2
            });
            this.scene.start("main");
        };
        sheetImage.src =
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAhAAAAIiCAMAAACubAEdAAAC0FBMVEUAAADJnIjLn4vLn4rLnomzinjBlIDOrY/MnorGv7HMnozLnovLn4q4tIe7mJDLn4r04Mf2483v28XJm4fp0r2/kn7Fl4KWb1+tfWmzhXHUrZifeWjhxrKjdWK8jXjOo48jHyAvL7aadGSPaFmwGAS0ini3jnselgApkAGle2q9GAKmgXAhIY+qhHMHqAHeGwEWnAEkJJYoJ6HJGgIrK6vTGgLp3L0dbDfptZuazdq5kH2TumibwHLvzLPG0Z3kp45fqjq7zpWIt2BRpS3Z167B2NKkwno4lxDBkHvx1bzEk3614Okqnw9trkfP1KV7tFXQalOsxoOcf4OvjpORx9Xh2rbB5/AbMETLWEFza7Cp1uCEe7WmhovrwamUibaKwNHYhm+NakIbrBFAPZ6xc09FnR7cl3+zpruSiXWoaE06pR7C3tzNh1qujF+Ydky3yYylmLm5eVtHRLTSeWGto5F1qcKtzotxp6/Xx8FbVqTgz8TMvb+bkX5waEVnm6aMxW7EflqEp1m3q5nFLxifX0Zfu0mkglbRkGm3MRxejJ0rsB/Bsr5fWreDt8p8wmHYMRidyn3ljHGjm4mKWj/NQip5nFMzMi22lmnASTPFtqBiXDlEPjMcVi28mJ4+tC6QrGFTTEdval5RfY+Vd3tPtjt7cEzdvKdvvVXpnIDRwKtlWVBrlUufTDS+34qEeldSSzD+/PWZmFZmmr2Kg2ajmMDdSS/eXkXidVsoP1ORh012UT2If3GXgmPBhmh4dmk9YnIXF1ult2pTZlK/onSy0Xn84V26aFahi2iipGEPCAaEQSuumX3Iq4FIcH6LfDmOc2qLgr6ejj0VQSM0UWU4VTzc6emwvK2cxc2tm0JqOibY49bAqkl+uLO5zcZ4bC4sCwXJ4rxGCwL4soOLEgHA3qTUvk/p0FZzDwFfDQHZnmp9jIb74pGRpZw+PLdUULmvosCLfdR+AAAAEHRSTlMA6SVO2e7t+l35OJRW+vi51FsFswAAzTxJREFUeNrsmj2P0zAYx0EMIMEQzIsAgVCneEJtFWVJFDlJlSxJowwZqkodQBFSl1Q3ILgOTCxchfgQCL4mfmn6OHGxw8GBBP3DtXEf5xfbzz+2c71rJ510XHeuP/953bt9IvyjhFvP0d2fl/X19onwTxJu3Ht69zIIdPNE+DcJD+9fDvHkRPijBBf9iTbcuvfowVGExX/aF1UIPTkRrpgAQl6MMU7z4CrbIBz16BF4SgtRESfCHyMQZoeUvgQAuaI2AKIPsCTxsnhpEfefHCfANf8aAaQSQJcnAAgIVzGSoDQN6Lmuh0tKAP3uNtx5+AgQyrkg6RMZoRCAoeh/JBhHcjghiNPGskga/XobNIQbDx896yKsITogToQ/SCh8y0qDwYTVbvW2X/l8e/F6s1qtNhfnRwnX7j161m2ENUyAMBDmEUkxrvPEvSyh8kmNcUq82U8QUJnXKU7jqDd+EJgbCG6S88vyirLagCcTrnwkwRCDCeej0Wh30fZ7u1nuzkaSzhabc4Vw7cEzQPSbgJQXtREaAleBW6WJZV2GkOCDosGEoMatCHMiatsfplJAR2igYo6A0AsA4apHUjWEmbAYUe3oLIE2u9FRna3OhxkCHe+I1Jdh3WgwxjXJc8JGsboMoWJeYgSW4nAogd3CcZ7ndd9GcsDTEFDKLysq+nJuU6k9CRCubiQBUniumwZwuomwHTGtLGs5+rGWjKQzBLQZKR2Bzw3dQPt6Ja5nB2tU0MHhhBmzAdc8xeFQQoo90V6X4AjOkAJVrDWEy7LdVkyAIAVmNS41BONImnsBoyCqFjbVHM425oIvETsLnY002h03hMUJcH1luhOvIIE4SgChAqc5SyvBOIFEmwkgH6eEmSpPcTGYENQ4buh7VMOSYSEx4ZMQAj8iQMWixnknnyUNBCyQ4tzYC1UI3gaNA1R3vbVNRQIJYyBsRkzo9YjLYWvEbrkbrc63m8Xho51EAMTTPUJqLrhDdTsgFEIvZ3OCXTY/FLPuNGMiQCOqAof0laVhOAH5fGao6xLqc7k+9liA+kVPcD2+VHBjgUQgYQESagm9sUSKKYy96I1lbNvZmnkibANGwvnIof8ulvvk77as9lJsNe+vRo7DPl0cNwQCV6KOA2bzeSXK0AZAqAQRh7dGGKLL0BNQt2olDNEMJnDV3BCFpfBqj0fNBGEIH6buliAMkRh7oUr52ECQK2cZ3z5Etg8+MxEWDtVq55wttzvHWfDKS4caYUsPXjtcS70h5HbP2QMfV12U7qBu9O4IdDDE7EA1WwoIiiEgt4MNIfPQzxvCU/MpDOGbCW6YJKFrHQrloVA1SRkgPQEaLrJBspiQdRZ17lmkJVw4VIvNhtZaOc6ZMERrgvsO10ZvCEhFEOOOihm0z9QNaCwYQvKKltDrLpINAQAgGA2BLNClDAGNphpuCLfAXHnFHJCLQuGyAuHHaYR+ao4pxlRzcMqAmfLMET4Q88HBEG/5kcP1VmsIuIUj3FdaDpkh+ktmY7vspRKlyxBcmxnCbpQpZqAhgId+yhApLBmwgg5fMixPPJ+KzWfeFop9oWAFb/AMwfSN+H4WdO84pCesHKotO3pLD+7zj5hFwBDi2LxkePiIAs3DUtdSkIDQLssy4oZA0D0dQQgIrh2VZWI3Sm61hJrQ66aFurOvcxaIzG1IWUXst9cEQlawQGIi+DipaOtLvr0tMFt0K8Er0oYVPFxqCai3/fz2nl7b/HsIIAgbOKs2/du9IRatIaawYpiWjBozxYWX0GR4uSjmPG4cSsgZNwSXqyzFhoEAgisIYXeiNBrCZorkAZUDnpmQ8Yq+1REEEiOhrAlLvse7ktR09PKan4V8ZkhSlwaCPJp7Q3xkhujE9IR30+n0HT86m05fs/fNdCoc8nZK5dxXDPFIQoBStkRIWax8jDFMwIBQCN0HrKqwuXy3M/9pCH3nuIkgFFV3itATwtRmioP+QhOKdJK5oQ2osaEiOvyHQD4z9qJhhvAsoYQagiTtBEwNEYfqOOie+N58ZIb4Vsku0RJE+ql40hd7I7xmxhAHVAvrmCEeC4R8JbYHSuCaYocUqHenSugkPrJbZY1mhlDaAIQyOyC8Tm51hHltt8o7245gLQV0BKgIs0zrNAhoCMcN0c43ERhCR5Az8unFC2aIFy+D7iZCTzifUnEDrKbTZccQSxba6g0BbXAJ/3utcm9o/mWEDwnRphPEbqY1yUnMslpBnvUEWRXzUkwR8CsZZDYlq9yeFMmnrCWapyGgDCrCqtEPJJc2hGc0hLJVePnyTUWN+u3FJxEaeHMupmCExf79gkdetauJYojHgJAuVcZsEyGOMVUdgl8AoRI6t7edzfc0255ZIC1BniFmtr03ZZDRI3h21RIyu2i/srALaLYUmK3tSENwqV8OFT0AdAO+hsAU1mVJDoaIyzI+LBmkLPmA6gnyYHx++fHbp08fXnxx5cd6ZCJcTCaTV3yumExY+tFmMuF/J7GdUF0YDSFbIqzBECSA1hm7AVMaGWdF0jR+bo8jACAjARSN7dx/0yRFNiZoqKWabBx7b5oyWo/Xldyn0h6T921AQ+AVGaFYj2OX5z+Y83efBnwWyGjAZAh5V5qwwsEQrBCYbwxZ6M2nDx/ez6WIsIRhJF/RvJ+Lg1eLd7TENH23eDdhDjEaAsl5j8AQScvXp1N5uAzJmKuYsxAwNITeJmJeCAIJpZCJUL3P+EnrEkGn5EBMA1rCLBqLim/YFyAxZqqjmRRoDLZ2o5RvnvhuuCoyXohcVshFwdO3QckHCD4wElY08Rvr7fLVRNH0rcEQfRWSIaBdumSoCsdcbn81GU5AghAqAS1hzU/qPHYiKfDeTBDO+cy/6D4oOQRKEyGyM1IU+domtJDzAsnsnBaIvc55IdIRVBvAGMrHhjnm/ndSzqXFcSOI419B6TwIIexpQWIJwrmIASkavZAyQY/xQbAhLCwK3oAvDgyYPA46LIbFAeFDDnvyyeCvmepua6qlnqn2ksKrdU3JP7e7/q5uqyXxMeMJNQid6ILgBIGwZCMwaVNBBHNpYiM0AlMZm0XT99WitRSjCRabErzFru+TxWZaIEgC2Hrd933ZWJqtjzwAqTARyhR2XATitK9V4wdBEjlOwQMnHjiYCIH4rc2WJddAY+fgeLnQQFVuGDg+wEmCLgLdzISfL+n/bq6HPzUCIL7miBeIwPfVBIExRGgEzbaLlm8yLZtXE9rFVm40owjr87jB2RcTgUFszIRFf9kk8twJMF90yCIQGyMhh4kKPHr+YhZIR7zK69c2OOnSQMBFMN3JwKmJfsBs/v2dtO///OW3v//if+HnQ3z/3a9/oNIIQWDi5oJg+O2lG4EPIQiPjxvZtenUi0zLRwu22Gpssiv3wygIHDEwcLUgStiwRB6kzQsnzXjgwAO0IHC4fJxUcie/OD13YhMhuyyCNR4uNhaJpTgBTZD2J6jh11/+xq+zHEj+Rk8RhCC8+FxNBlpiXwQxzpWZhpgR9Eq3dT1ovztWCMyngYDWurznXLpCIEEVxPGsN0gGBjOh68eN1UYr3h1FKAMHvgnMhM06y3b9xTmcsux0GHt2l2XH2ESInKJqmssi2MpZSYeLqpCO42wIApaInzH541Tzu994lBbEvECogsjVXiUaMf8S1zsuiNjtvSvTqSGyXgpi14KLojIQDp3I+zG0ZgsjY6Cm2wA7ukIQJ7FjIgVxCRxEIDML4mRZw6Mgdpa1G8vFAI04GQVRqYtgaRGDk/lOyJ1VyMBpnNqQC1kO9OnIn7+jPxfEi1ky0CaCwGRoHwMJcxtc1225INzuQBR8grDtXFEcAHSeBChCvZd7H1331KpjRgiB4RLwKELYuVIQrrvzVEHESoAgXCsIksByvkyfyh+4ns8HiCIKL84SnKomCfOTo3Fqyv6RIaZVCCGpHx8RbPIFTewyFTapEExBaIRp1mNI496DBHXwpEbE9QTPdeXR+z08mWiKIkC+Ra3fuZA2S7G9UKYMnClCx3eUJHdAQcjA9hLoKQJIYM8F0fWMO33HBbHnTIsNHRfEHjgkAdc89OsyIhCE4xl7Uv+FovuaIH5ExNRwaUpWCB1hIGzdrr50jhQE5vNKQibrA1jIBaEaQeggicLao7vDPlADe/dMEDyZbbBsrwpCDXRuTxDERwZB9FKSgxDgWWzhP87ccRRFMAuiNfQD5gy9u+nEgM0qhJDUT59/+wXTv52qIJZK5VEQJsLO7Xb94TCc+IdHIwhzRA/dOhwO/a5zd9MYQYC6fjz34kXHVu2NLfxhwMBzBLmjfFsxtvhFkiRpIdOMAYoAGjgIknsSHbEVjvgQp07kuAdZEITrKgRBUIeLHy7e7ceX91ISaOxpQWgXCXvLFAWxwmPAKoImgIW7vQu2H7QDEdcS6kEQul04ETxN8PqjeNFpi2LQAiShHeSOO5EOX3aCFqAI7Ox2xyPoWBy63knn7KHTDcZ+8KM8Xz0Kosrz8lEQTZ7bACMJWBo/3vPtzceXYB/eqHnQKgQQvtIQzPKS0p7YOn9iXqoT2PQB5tUZ5hEZzxEgNiewrPa0QZAgSMvqVlsQmAVoAht3ZJevRpm0WnsoghceDspZ17pDE0YhPgqCO4+C4I6JgHb78sGyHkAN91wTtxhg0znEVxzxlUSgycVdzSpdUwRBlwBm+n8R0K4iMNxqZiLofZFfSzCbmVBHtjB5YGoUJY/GF8cnCWp3f3h58xqKw411/1IqApM5EQTUmJ8QgYTKfsL82a8M9s2cgD9wMP9PGiMIDB8jSleGmaCLYhogCerrGyiPlR8ETQopuO5T4BsRDk24LIIlSSUXwdbc4StiATilnTbgiLkdRcB3eScGC5g+vOZPPioRhUAgxHgRBTGv9l628YVW05FCNGLK0TI8Jpgg6IYKw3cwE/BN9asSP6kNySK9DBXJYq0ySILZjISmzFux3p6Dk5ZLcOrG3nBnvfEsVld2SBHUDrjjOngj/gd7i5+ATQXx4sWXiECGONcssxSL+Ziha0olENVdT/DzhBnnOYyZgKb2z6cTWGMvUrFevUhrNWDoB7ZJqirZeMLxpMOkswTHj00Elq/xTBAvEM54YKqUZ5kYCGh8qHhn8XkE2MMzPSkRQFCnIbgUsw680W15A+xaQ2gE7WoCLw6SJA+17BIEsHbpJ/4y069Az0SgNRPqHHbceMrLtYCRkCS+nPm10YJbGT4GgthAwNMoxC/FsJDOqlacNCMIuAgWPnXllmiRqQ1MedzAOMG3YLez8ZMQBO6WCwmm1ZAkTSW0uRZCZVQ6tZqcVY60IvEwNTxMETapIy1dTseJpRqgCMHKkRaFE0kxJUB3pV840qp6TIwYLzwlQBHEqrm/XAaVE4l1qgocv3Iq6QTgRE5CELRFMOs8ZFk5quPUZxktiLl9AB3cfhC/OlVjeoX4/EmEl5QLxda5elYBIjQCU1TRFs6jpWr1JglLBy1QX5MrgZwiNA5ajM2ZBkKKUOF+BSR+KOM4rkqRTAxkBEHcAwHeok5EUwPhhI1YnvSdpAangiaQBFzzkIIIlAt1TvCVKFuqJ+dl4C08xHjxcH9/Y6kRShAqgzWoh4AYv1UCMmQGiyYP6zrOoYMznO7ThMhZJcu6rjd+6qzUaQC4yUYEVk5EdQTIz4cdw2WychpsjxJoCichCB6UkGDc0YfErC2uiksgvgQCOp11FYEE01g6UQUyuxSsMOVHIKHEfJog+pkgOloQ87kYHKUc7cMdhmaC+FJDoPUoiCUmcoLQCervtrooAqGDWp5rg3GKEDjpphXzsMgJVH35EPBEIHVyigBlOvbEVGTlTK4eiCDAeCAvnJAipE4VisX3vCjqiyD6Tg0ERZERBPLKLTwkTRK8s1wEO4gm7zouiL3IRLZzDyCI45YmqBXiBuWgTCPmk8rPVYR2kDBBQRzUqTo2QiPMZvM1VFjITCwuildbwCjCRhaASrt13VINUIRAFoCVs8JztcepgT8GCILYMZBnonDhDPtREF4ynqKShgThyiu3aAIugsnl1YPcSmcL6oAtTcCEiAMRHx9u3/DnP9zeqCFCEFMbFoutMCEIFAMju3LKyOX9IWq1dWZJRSLvK28yU8ZAykwEbyUEUWk/Pr1CCKIxtqEVghC68vr9Xi5GZSKQ84Bv/hSx4dpOkjBfBNt3YjFHLpnt9xlv144SxGyacPdww547iKsKQhAQgRkQgigtYTiH0NOpEzCF81sKMdQUSbAqmXcLbRKIqDbotxSSD+2GITQBbxjSwjdRCsJ1e7w/hJkQF1W1CkZBrKuqeBTECpyYJMhRojvuu30vxqiLcxCV9+Tuj3sXHIJAnbJ9d/P2jV7vAcElNRsy0M6LoyoIphcZjaAZ3kEGX28koCAwn5QgviQEgcYIQXxJCOLMV2t5fk+gCDYKIjATchusUdepEnWdamkmsHq7xaM/4XabjY4XbmNwCIKWzrs3t+/ePty/fv3x/SucWLLnBKGfU4mC6Bb9JJkMESpBm4XgHWTUY0wEQRcEUzNqFsRndIVgKAgTAQUxjCdVsDNUChSEgZCltrB1qFxNntZcWaOTEYQrjSBgyu5ABv++fzWzt1q9NzXi7I6CcHs1mVd/DIa3FMIhCwlGQcwV+umCmDfnekHgLYVAB+5xd96dOlecm1vikEERGjsKNpu8EktAqV2BE1R2JJ0cnMhuSAKbNPxZh12RCxDD3F6+es+MFYKpj7kgwJhJlXoGYztN05XdqlcsmwlNCa8qU33wq0TgGkEUsKOdTD8QBnwzoRA7ikKQ9ac9jODnUATKNQ8YBRHb0bKuN5UYbnO72nJHjBOBfd7U9TKyY4qARovCWK3BXksRvIcy8e/Du9s3P3CJvNMIU4TeimEmCMw1ImYEddh6/pZCjCDgGGtHcmf8Ny7LV0RH6LcUQmOPAd9MKPUTINTA0kjIzmI1qhZOHXFnJ50w5U6TUQRcBAvH9Z1GcXLu1HQb8FO/ffXq9e0PfInr1b08YQb+wENUhZiXiO2pQ0F0Q4a6NDUCIVlT2sIK31PQBkIc2dLSzfT7sMEATVimtn5HIP4sHwNVTRJY8LhjZqnmKQFTP8SL9fo/9s6d12kYiuOA2KnMS8CABOIt0kpXRBW0paERCCm0dIjE0qUSDFkYkLpmQFkypKnE3CkifAHo1+P4OOa4NRyXpxD09N7eusf+xUn+99ixGzeiO7cgUTaJNEqSTuA6Dq/1jFhojNvjVHyPlvRnCHTsZrcbIbxBHWDImLW+K0LEsktNggCrfa4HQARhQEAO2kZmk8gSpjfIemaRnuF4zBEmRsZgq8UxHAOOMKR8EY2hWA7+ZLxe43izshLiXaV3p6hhcMkpiBEM/MteyM1H2IJNprLjofq6Tx7JxOjmlCVQJwEDAtjq9oun/f6sawYIwQsCc+C1N1hOgoBEjH5+NwS1XXgGn+FcRg+O45joTKODfa5oIucyFo+SGwltER2PtGPEHYjoRvJIzUTA/fe0WXQ80I4JQ/BBwJjxMWSkdqdxBNIBH2R6wBD2vFGHJeCMGITFJ2q1dbk6xXT4JMTEg1AuSvMyZgl07N7dfvv8BVxpUO+y23JHCALI8dLdCEH3vAi+EmTjKHoQ4ou6k1A3whFjep1k2kxZdCY6vxpKTxboWIw6jzjCs04zl/E4UW29pgzBIdAR3XjNEUYdNWUR96IbASlq2zFwHIeywlOvLJWC0OqopSAWzsZ3PJQjmpNYJUYgJ/wYAa73P5WXQD5PIEk8b2Tw9vbRuxcvXry9/ZYCBBEYRC4jQjrw9YeEi7l8Q/NdqqTlX6D3FEHbCesCxVQDF2GadKDBhadoZ4D0MTlYgngQyYzyiZaVUHP65GAJfqEzjoItHfsT2BlyMHvhV/L/qfCq8MsdQ5m6szBcezUIwst8hrDnfRkcgUaNoFf59s2rbr/VByWgPjYtd4TQhfXNcKFZZEERgh+YIgratINGLPQ7B6aw0IikpW2IjiG+wxH8BDNOzI2ajkctJyHCjKlZ2nT0XIQCxzcXawyzmZcDqczxrh1QSgkhI/dqhrDnnVssgWx19E69ODqSz5+OurRbghME3ZUJli0aCcZlhX2I/YaVBHEA1InAOnHLoLt3o+7IQiP7QvwZOoZuQoIZi62tCnKkbkKEGXv2LkW4R6WLUHr1IPbHBU5NwbNKpDIxT8d+PMi80lWHB84Isb8gZt3uw4ero6MV2Dv5DOMR3zFSOVC9yvm6qtZz1Z+I7bsJLIIVIl53QlpBhmZDOAJYlsintf2BXXwvq1pOQpLRCjJk+r2kcBNQTagcQZWnpWV6TkJQzeVhW6gIW3mUKNd491jAE3ASbDiMvggCEjfHWhCj4fCG7zwXTx9uPr1bvj36hr3tbxHsVse8ZJe1JsMukOt0UogwV5BBVexECMETskqffEGbJQcKgicktIIM7ZW1ggxDoCWFDBPoKEkQHAEsDKEEJVpWgif4z4xLb39oDJTFI5kYuAhiecTbR0EE91yGv0izar2usjTwt0Ivr0rTaAUZ0wSjazrv9Zpyownt4ARhryBjDoBoxz4RglaQEYQRjSDmzgjhNp5AyzonY2MQBgemHjSJoe+ow+Ye2sfVh4ddZQ8/LPGdpfJs9o0QghlNZ1VJEBJE4IUmmiXYgqCATYJwEkgQppRJEG7CnARBmkBHiQ6eIEwhU0qoFCW4OoxgPGYAIx44ZBJFj14PgsdDGP3QiR7OqLB7IT7d23TxtGt7+hGFsIJXMymKGS8I3oR6MJXYkU2YeUoQ88I3OfyBCJqeOTa5hMQuOwoCHRyhVD14yD4wy2PXvkBHNuAJ6bxZKEZNP5AmwFGSgyGQDJnpKYaAM2LTAE57NMC1GWpMYLiYRBNIpEkS7lOHj4YgQARgIIoPTfxYcYIg/Qp6QVLYM0JQHPDzL0sKebkgB0vA3CgI+FuYh25hOFKOkEIGFAT8XZinpkCkcgQcocaMakiO1qnS2yaHO0LQ8QQTVrRgj2Q4xEtkvxmYkolUJQaJTEz5/py21b1Pph6Wm1mrv1n2pQOE8b0RgpRA/W1C8IQSzksJuf2ywsNHFI5QeXkRyJ0ucm9Oh27bkXMEmJXDRaHleFrVIiNHDf/hDMH3mowLyFgbAHSEjaNgCOahsyOE+x/DngRrTSBBd27JhO8moAray+ZVf9n+uHoKf+EX7GO7vRSWIM59ow9BwiYp2LthE8x1quGgjVHQmZf7CuMmpF5extinxcBPVmhHCYGfIWBj4+P4CcR32iwOCQXogMC/4AjQ2GDGEDIGdAqVQ6ADJM4QMLvdpRI7HoawMwlGX7GEVsF+oSA4Am4LT3xff4pOKUF8/CTv42q326sWIwg7RBDU3AtCuAgDGZvzHJ6y2ETwhFKu7JXLp9SQ4baDJYhCZ8wXVFqGKsPBEvza0xkDIoDF2gHS4gh06OxQSx6O4L5RJ4rdBLRu++Huh2yX7eVmCQHCHoegD26botpt8LXAra9HsAgmA2xcFllWlLHBEU6CCNI60xe7pEdyCI6gr5izOt1pptBRkIMnUEaqNznGPIHyU8eMUhrKEKxJMJway4Om5BoEMQ/5OtDZm8125TlbtqU9JIItCDAmQBCMqQRdZ3AknuD6smw3gYlwP0awXrgJ9h5b0cJJGGOTWanoGuTqK5YyXyawKZzPa8ESaDv2Sei2wTY7daBbO4QQ9J30+CAtE9f499YIm8CPZuxHsAvY5ibYEPHdBF5WbgIVxV8CuQmyx7PQk2AgBdl2FLm6xsHr6RpkwROoBnZo6uoOBBEsBFE40xmFuHgg/E4CToL5zSRYNS/VjFggE3kZ+nFQeeOfqMOsb9VBIc4RwrTdelt+QBwIv5WwqGSvNhsgocREjRIQ6VomivDX1mEbsZUZE/izbWYkBMSB8LsJwszow6+R+OV10IhzgFBmbsNeKIx8aIA4EP4IYavQ76yDQoC9Fz9klw+Ef4vQrEp0//rp92fO9M9I6+ufPeziqX+X0NjfU4f+n6gDIs7ev3b9+vX7YNfhgT9k16Rd+LqdPXni3yXA4z+sAy5CI7Pd1XYH7VZjlxq70th50+4eCP8cQWrqmiTcIYAsfwcel7TZpdHuHAj/HgEQioCGpaH8JbIrGmATjh8I/x7h2DloUe5CSQoqnJTQruLP3QPhM3t3sNogEIQBOCGFhiSHcTViS0sOpcUcevViKJpI99RKHsJzLz5B36Iv0Hvfpo/TVRG3GcwYwUC38wdCJmY+xkUScllNFEau+mapW4/8xNwdZs2CkcLo4upxTXSrfhb+izCajZcPh7nXg/+mFG+NZywYKXA4HE6HLMar02PPWTBUmK6EdXrgc86CkcLEvrH6EOKSBTMF1+lHXLcJQaAXQusJBCXQYWFQYWp7yxYCqkf5CkeUBBaeixshyZe4/ExYFU9FEYSZKrI0wQI9A0oPAVighYnteS4m9K4GwQQWtsUVINXTXhVRXRRXxK4uXrFAz3DsNLqfhRHCwOtQE2jxQQ9GhKMILBS76KmDGz9UhVQFQBD6eyXILFFHgtSPkdBxhia9BTiHoBNDCDDYOixcDxFac6Po0QksJJHcWFbqp6LcxFe+AURyC1URA+zkhhCqAI7jNCMQAn0Wf02Ac8wwcb3b3wR0iUZggd47jRTa8vWdf+Qif6dnoMLCD23X0+o0EMT9AsKyKigo6KkeREwo8dBS0jbYi23x0EMJ9GAogV4sRaR/FEHwYouIF3kqoqCIHhTxJIoXCR6EwIqHhYXcks/hbNLtRtds/IPzSl627P4yk/3tzCa8NyMlT4gDh49JCIlQKhJCRSjPrliGUCBfvtCAJvRDkOy/caMMoVz+N4J1od9ud2vOP+jQaNltu9X81biVu/AWrq9HKPqzbXfj/lqHfYeOSQj1NmDlv/JUCBWhnBB6BFWOvn/y8ShCNA5JSFgYBzSIb+AyhHIr/ivCOVGztM/t/RsEx94ijBv58Vzmi1OZeOvfsgLnGbDcnDhxAv8JIeSl5bCfNNIqUetbliREzbIkIc5Z1klLi6BeD387+/Drty+fn5EgIEHEKAljFt8TCP/BCrXrHyP0+UR2+23+y/orHXhB6I7d73JiXfrJPRinThmL4cIDSkx1VkgKbHaXXc1OgKzzeBpC6M2XoidEBaSRLy/Vy+eSLyOEcsHLj79+fZgkUURpSEjCwoCF9NmeQPh3K8oR5v7UXyuoxQh14EFTFLu2/0YHXhW5llKpDme9vK9aeobhHuSnU8Mw1tq5wFx5oMBG3IjRiVRGQqECQiBlYYhPQV4AAaEi4PpYlrSwahPeyCraDlrnRakKLcJPoW/v28PjX78+iGLwDzQK70eE0SgI2AsdApYfLKxQ/YYOQRq8HnqZg16MlhJBdx8cqGthIadeA1K0srrU6A916HK/gC/UzlmoB9VD855uaBg+wv5oehDNPcPTW5G6BEEIPIXzVIY/3GKFEEeVyVAtkMMlhIKQuQTIf7TNsdkWjWa+0dAgKGvoxUPwD2evgYcgEYWf1yRiJAEvcaMcQd0GCMF6K6TBPF5LMUYSU4PQr5x0oOQJXwoYzB6Lq/++Ds7JSgs5fGl1ery8Xy5oLA1jmHoJw1uhKZBDfx98Pv+bOeLintjKzJUm/pIQWBJCza2YM0CBUBGyXLy4BgbxhIoN3mhV6rwx6WXpWS/oEYAEGO0dRZm8//bw4cNvr8IoSAi9H0ZRFIZAB0bjL8UI0juoGZH4l2VWyCE+xOu8LJaCXxqEcaWLGkD/Dq/ydEGETGWFaRAgT+mAr6AxfCzcqfTlFExNc44WpunB5yD2TFd3J9GUT7+/C33zJSeFLzXREgI3Lpy75OQT8OD0KHdFWZeBbjod+3zbtifn0911b9toZW+p0sakjkoI8SL+8CXef2NLCfxi78WLdyzkO4gwCsn9CLxFyEjyWUHQbCHU1Yl1Ooheo8wxLNzRyM0ChzeXLqcAweLusV+ZDODQQQMo0KW6CHFaoAMv5tfjAxv8YENrN9A1PbQyTZ8fpmhoLjRWYLdarc4wysks+0KulGJCbJ+U2oLPVhu2ubyQz8maQKtlXWynQAlZRH63qeSNXr66gVWyOi9//hIwGjyjn/buClPeJSRIQh41ovthSElCkuAZ1uigFBAVhBYfnQ5yhJ/FiW3/1SJlRKmnHGST2E9r0GEM9ChyEBpC2OhSugGfVGqI00MIpwDyTRMQZuYIuTpC4GF15lY3+Yv6wBBXaqILGU3gQrffhULpKBPn5Did/05HfHNOdGnrCNFq1+uTXe40u16XyfRu1utlhMA0gSmnESMkTt7uzzpfvcYC4AALwjAiLCRBHAekiBD6ZEa41EPIXkszi9Q7GRkgbtpBYwWunKnDSjjfGNhnJsiBDKNIeQYW7QId+jCwx5OWXqicuQQLqS11d6semlerIwxz66NhdVMc+DYzH0+rw/xlN0CINT8p30M0TvYdHhPGtuhs9y0oyN9pjs/tCje3oIvVHHc1hFCT6Z1XkumBDr9GOPhij7EkCYMoIiGJaUjZDa4OiV9TQgiLGAsoicOIhqxgD6HmhZqvfX89l45COogShCHnw5LH3qk78tMAboCsBXwRwuSMjZys0ModVBf1Y9TYVYxwjg+yU4Q2r9lyW6oNPFgBD7jM8EFY70VWHBz63IbqKJcIwufDRu5w4yuZ8NSQ0bM7CF0Qr9ZAeicbyOqCV9jZ0myDs6h3WlhLCG12xYs6QuxRFlEasDBkJOLPmOAW7iKQGy9JENAoTlgcsYDFNIjZ59/wENgferv9oLtSthC6bSlaGiAwBrsGF2+95ciwbDqv85DZ5Iy4zWv4TJSn9qytQbCgyCeyngLCIwe1ONxODp6ANY85IzZLNAJ2aAMf7Bmm2e7Sq+bEXW/AMPU9BEeQEFYHooItL12D84EDjHCQEGcMX3cFhIKQ5S7nhLiZjblyixPi+QBlOck5IdJ0zQUIV2HW74cxozF/nHj9GuaehW8gbuB3JGGMQaBghIZJQCh7ebBABznpS/enZwRvlCNLsRVYBoghtBZGJqafRRGTg+gQrItnbvXAk14ZpJN6pSgbHi7W4Xla3ql3pcFrjpx5mh/upu+V5j7M6Jq/YdDMBXTj208uvrmTxRTjmVmdZz1UQhzJIJAghLjygJcEtGEP0be1hJAIIsE1ECJLO377dJZd8WIjyyh+J60K20Po1wh3n8G0ByQKo+A+vQ/HkBIaBPcQ2ouTGM5C4EkQMEK+wsuJq0U6iHDtnlLFm8oZ0ViRdlnAzVsBL+StXIKLgF8iZhQiXIF0t3fwNpfvTfm0KylRpoN1CwY629y5F50fdh8zeLE0576Cv3aca61AUzBBPJ1kMlxtGwuhThkhhPRbvLpsp2lZ40u/T4hHF7+zd3WtrRRh2H/gOEZRKYogphSp0aNpsOk2aZbESj6ai0BKaSgpaSUgqVZCP5SAJVDS0kbxokopCtFWLxQRFfHjQqleKILihSB4p7/D553JdDaZ7GxVBBUfzunuu2f7nNmZZ9/5eme260LaRyQLVq3iJWHOudxavkOG3GZvJMM3v16iY/kbJq9+eRU1AxqV311i6uLnN15gb33/0/evoj75Hg3OX9Hj+GEKknjLPyOAg9W+BNBnBBa3+95ie8uTETYGjkxbxXEVx6WtA5JHBW+aPAA2hpb8PlUHh17UGCHjyrIw5Ol3z3pnRJQcbBVtibHHw0MaYjiwPwXpV3mC9dVVEkRIuYttPtpD3HlFwceLReUN8nlnPM6jmWKuwEqJmuuUpCAyxWJOC2KYAUhWO+2jo3b1zBW7U3fOYSBThHEGQ36ecCTDC2g1XmJO85ffMDqNWuO7nyENeIxff/2G8RB/9tfffvoNHQw0Lj/9fArDVa/5pYHTOyBHEJZ2nXghgYnoubR7sC2vrTMBy1Nw4W3lW7RLeqBrqzDl1UUy7QxJlCSh2jJ3LFWisDNgv22JtjvcK+BLagR6x8KgRK1/b0mmHc8AoPYMFIT+BPt0mJDKpdBansvqTx67dFqwJoI3aXtrx2O08mqb6HMYeDxfQaCwX/3t8td3X73EINTld5fQxHfkJj5/i7jeQQPj558hmp/H+JOnUz+chnzSACxKObgisDcD4JBKb+EyIJtZ9qeQSliUb5OQ0KFwGDvyajCDQ3tiN6P+k2wQhZ2h1G33RH6ZVc3OxtLi4saWPQ3AAaVZIUQ62GWiLoSUJKFFEOma/oB6IZsQMsj3tVHL0XQEj9fDhFzJkgg7bFn53uUlyh8TFr+hqSBcA7oc32OUGo0G8Z69MDb2/a9vvPLNKzDe+uL0dMzv/ZZ62N7FQFpt2nEa6WLSaczBKK6vklA2gopTeYhDqYsKTLTDHn5GXg0WhCEDcxojmMHSFtWwMyyhc6RSIBrJ24yOq+uayFcQDWihgFGncJY8QbYRjWfDdc5oBDWV5LmU+OhPlm6ph1N2Z0uwGH5vxivfPImix3jDL+hs0JQFnAWaFJff/fr9G1cqfyGkSD455SPToOqLJac2nkgWE/1QlUyuUcQFd1vWGsFVBqeePh2ADb6zKMf4DnCoyHv8GIyHNT5TpRRiTYNlgu7agtgmCUjsHIhhtu1Fura4VNlY3zGrDGIQFLjoyO8xO/W6nLSMc7dOn8CtR6gVEUmK7/QWcAtP1hOSYojBfAB9bmAkA3/7Z2gB/Qwx2f0buhsYtvz+EubXIxie9UvDgRhQbGDiuZiBEOJJOInpBOoMZ248kxe9yC3fNOgU06jeFg0NSmhr3f4UTGtqeBjdhDUnjRl8cxTezgANLJIYKts4M1Ax5jLuIIq7+xROIpKtZ7NU5mKcrR4vZmkANdUowEowoJESt8xFJcUwg0QJHtrhowwXRp5JjGTg311eYozhJwTDoPP5Gw0/XeLSrxic/IYNwzcNfFXqoebkxjPxq1rcLWBWpthXxLaFQaEifQJ/Rulho2+EVLHYGZQnUDA/R+LLYEjIuw2tZg1KA4cMlqiOGI3tQQYzEREgweSodTcbqdPMJM/VUxgZcepNkakR4NySCBUKmBEDnk5KGnPMY8T9H+OFX+EgRCztb/jzC3UxMVINQfz009fGjKFvGhbpWR3ooaaHXVXacslMbYsEs25hUKGLKPt74RRQWxAwaywdxOF1RMkMGBcDJWUKihl+x84AXwkVr/oIYtEUBDHcfWufIhpx3VYPx1IyG2UlPV56jovJetTl8Byue9RWFAYDkICPLhRQcU/DwEyZNNIwMtIQIYI+DK98//FzT8JHoHGJaLnv0MX8FQOXGJz67SVmwC8NW6JKqGWgB9Ieny3v33ff/kKMAYXxXGM8t06VqYVB5boYH6aT3cri0kZITiTIySFuTQMf/vAIwYzlsjCoO0wiyR2cDxLrNPPy0APUdqhsYEpnd3f3YH2jPyqzFCSILGPFnpi+LjJ21GMKJRpc7EUwHpuvM9ayCiKXaUTxG2KxFsPSHBju9LhDRs3hwnHnrY/x3iWqCmjikpqX75KTQLvy8svBvLQxkIOozI0Xc0KGs1CDxEmMkjc+XRhvbJOL8GPQ2BLRRjv6wroOP7MUp+kMoKjt7e2lA2bAngaTiBtx4AEMB0j+xq7pZkIV6n6bbQgwaEFU6avlrFftQQt5EoFCnC502pPNfIexrhbEMAPA0zUKw1cxlVRBZBJO3yjCyBGvPwPHMAPmKiAIak/8RAecX37LvLAxUL256o7n0uNzMBbu82BFBHk4mdoBVSq+DFpzGyLebIP39bEoTLK4PQ0SVzfu6LB5Q9f2NHBv8XPTOwQyWFB5YNf0ECSpx/sUzhkJAp/anHTkt9g1SmJmotVpnpEg8J1eSTHMwK67LgPwZXjq+ye/++4HKIF6nOiAPv/+Gzj5dnCTfwvDOjmIQhjFjrukHvbL5X2lCEglHhYuYsuHYXAiSYYgwttWDuX5lucGfwbvyMHuqgybf0iNiWlYGPx67N6MsDEEB8iu8pGCeLxPkcc4q/iubqdVbcJbTHZ1CtqT5/SNw5b4tG4XPxWFZri2IEry3MbAXzn9Dq4BnuHdy+9fYfy5r78JMQ+sDDT7tJVJxSlOaY1UUI4xYPaEznGKKiuT28BNGyaD2Wms3DuIZ3ZVrgY/hTzIsPkdFTa/63URNgatfn1ujk/xIIbBFgif91AfmCF0QlJPyC89Nifb9I9up8qb1bNul8qe8b6D6CSPMDvRnTyiC05VC0Iz/EkPYTIAT06989vPL7zy009PfvqK1rb3jz8DTTkkw+lUhrP5feEVJHgZxgkNtM0VsiEab7SkQWMXjkFjSdcX1qfQilJh8weIsMGo50PytQxmML29OYDNrQwa8/PyyFf2kR0GRgkCoqLvpHbOOpNnedS0rTMxca3Qm6z2RKVRrZ7hlp4rKYYZVBhFOl27EkQunc5eCaKQToeVICwM7PXTEMXYht5iI2Fj4FTUc2EnXJAOoqwz80S6iFqtGHa2MQ49msGo5dcPr4LXtwbHDe1PwT1h86FtGWFDYfOa3M7gfbvVUZxoehuDfgC8GJCBal+fzBuxpl5BgOH2K4p8t9t01eNoMXrdlINbSorCYNATY1eCIMMxVm7ZGdgXPzIFM1/kwY+B5hoquWySQp1JAcs69bMwNxnLQS1xqlh8GEzwg41KZX3L/GKjPwMfHTbPVuGYNOwMftCFYmfQutm8b5bNl8ldUosqxnwqnZtuJ4rbJcVI8KC302TIJ8IC/YEpcS7D8JN9Y9rOAPBTCMIGC8MBtQ5SqTTNy01MTJx4H2VvYgIOI07ug2aC+QgG6+o1bXBbGhS4N2y+osLmV73/g41Blbr5NugbLAxe5c7ft7+8D+cww4STWB6q1LQg4GOeMARhpseE2oTGZEiE67m5uVxWLNSpk1FIZOVCnXCqAAMx6lYGIHT6BRsKoh/yEr4Mcra6npoOu2wGgljwlt/LExMvkyAa4RwJYseSBtu0DNdHXwbuGza/OkhoZ/BJj0qFnUETyN5WGYYQxIJPaRoUfuB/QBCFbBpVijsXEUv5skUY+UK4QUYdQ1Y8D48d9BhvQRABfopfXxB076AgkhZB+MlPnulzez4oGm/Y/AFNhmClhOefgxiM6Q+tBKsojZyLUVUxT0dCTFN5GeRXmjwUznQuV0BxEtxGMqpTXSJL3TJXjPYpDAaAp+sRIJEkIxoXhhqYypJRyJNhMBiC8HpIbvzxYVBVRi1Nq4NuCAHoMpZVxnTEiegqw2DQMKRgeghfBnEww+YxNzawrJL7Mnh/Whez2xi0kE5kRVEmPawNUg0JAgyyGcLVBhVpmtUcz4znmEI0A7PgslJK3pFpSIohBvZHV26ZDFoQAeB+DDQjRY1K8lDliYkbyAaFWXgMalRCEHEqIUsa+HCRRLk29ImFQQc3PUJh84ekByzO9S6T4TaGoREI/cPwEDaGK6yIemKNHMXyQAL8BVEcT82li/FCJsMLmXipOJ5QnBBEzc3nMsX4eGpa3FKzFedc23XPrtZlHLmuXqjTct3rCCJEjUqjJaWO9qykeJbFOZT5OZ7/xo0bH+rsexkmcqNeL0YcWuI4msHoSWBzIBGHl5jOWz2EZtDgcrRzg7HdjQMuw+ZxNVDWfjA9lpVBCzt03wypghqWA77PrDLAICncDM0/lOIZxKSWyCk4nhUa5C6SmTms9aS5qsy0pBhiYKMW6sQZ6xgrtwAbA/vxlAe0Z7gvAxV1MhJPYcJ2/hgSeFHly4cwUIPkI2jyhkg2tjTo/4o27FBI6J077E/hbfbtIEhajlTS1MiuJucWBnOLC+4TI2Fl0JJYkf3O/ZWFhYUZfR3wEwRNPjFWzKTjGdelFZqebCnRDGW+kXEKFIZfa9iyMnjlVrAgvjgdM7JEGUFZuUSRTdlUPAIpXjwKfCh8ZOwDOo8hRZF8tr1B4S5+DN7cclPjA4gPOIjrSEqFzS8e0mF9wFkHMlh7fzYGcxJkbf8+hRN5ZVQb4jYPhVqXkaB9kWA02OASDbQjinJdhqIwGET0eJUE0ekKIbWrJIhOUeRtWyzUOWuSYWEAXp96ltlgY1inRsRRJJmFi2AfPkr44IMPjsXJi+Qg2q1Ikqr0HYPBzEpHuocM9jGoSUXkjOI0GIzxqxBNk+qNOwx3bTJ4V/aYb4a3zvFl8A5zy1FKjRlGMHsZt44UBBZw1qZxVmMK+UyJ8Voqk4sOCsJgkOuzIIi2HPnGAHhX/pRGE+oQPy0Mci7jyeHAEG7kjA8Dvxd1hhvpFdGKgCIe9AB6wFJLJ1s/EHFPfgw6t1yhh1QDpzDmxgkFnc0WhuEJro3Fw8XKrtFFsDJoAhN62svC4PnlBZrzXZkVSphZW/b+Y5AgpAJcloQ/cBmu5kvUqJD/ECyInlxn1CJZsI5Yw8ePIAgYHUHXvoYgnp0K6GbYGJakiyi2I11YF8dKDh/MwmxHWueRJjmIdV8G/XanZC1B0OGBRaZhZ+CBZerDYBD4DI0ES0qxLK8sj0yA4SHAoBMRzuVSif4eDwkaZQ7nWCkTHsfZXEYu1MniFi2IYQZZS1TPOtVOi9PtfaMrVNYTk2fCsDEAY6enxrAtneqDhWELLuLe3U41eSa8FL/48IPj4w8+ikk1truT7XVyEEFp4NSWBoS+S0knStdyVH+UVNLsDKYYhh4gMB+MkWKTycJgiZCZmV2ZZ6M8xG0DFG4YSEUFVx2zEXknXGMurjRSsBjBCQMJNTBlMAjwfLOZZ8pwmk1XGVGnmVSGhQH4ceoVZgW3MCxRbe1ghXFHLBvUyJ9NtpOTnR1q3R1Y0qAcREaOyqASVVuQRms4mVN8dgY+FFppFqnlKYwOhTkwZ6bBKsr55dm1lc2Fcvlkb0LNAhOXjyCwhj0XFsgKl1ioO6yYDeMc+9m4NP5P70mif0vDTAQY2B+BneGLqc9H+Ep9sDJwKvCl5mTHaWNtZUlxutS86WLx+SEJxsKgUKT3A8d4WKLmyjcio5pjtjTowrBPDQXlJLfXOTYG9avLCySCYcSsHgIUxXBqutFIF7JZ3NLIuskEZYf0GwU3TW32aXVL3ScRntTbjOCsfG7qR24ZgrAzYPwHWEpWJ1tNtGF7R81ms3t+hjOsQ+8IPTyzw4Kzkt6QBvIirECb0ibUdD73ZfCbivBmhz0fDDGNqDiDGPQNMxOjUA7yEG62nnbyyblsjlFeZCOEBk7FSZZO89lUOp9PFsJzf95D2N9v3YigjqfhJ+UhuDAqIjo6j2ifZLM3KVE9dxAC1N451Fv7BjxFBr6QM5YiR5EWS57jjKXpYH+/LVMRf9hDWBZ1XjMNQN897L0MGaysxZZnZoI9BK5F4xGg3mBAo56tR7KFeD3qZBspXM2mSmIzWnFLcnQi+gMRxblcbtphAqViwWOkychbHsNTZ1j6GTyYYUn2+lsIAjtKOk1E9TjNcxjdA7GX67qVQY3GkRCkh0zAztNR1hkFu7vWjQjj3RaYWTnZ3z9ZiAWlAdshra5iuxdzI2HeKNRqtVw6amPQv1XGrG9sXkz3rjHCJiZ1VCXmLwjGsVYrm2B9lGgH0t7RWRxO4uqiS7ec2xLR6I/01lyxbZ2EUFO8byRsj6Ge+4Wp01eYOWyrl65YGHS49NJWS+y4AeDQa+4u3qv1EMCgFNDAodiP96pLmeRg2Rl0Ok0xr1wNGM7YGA5WVez++vCrgCVIapbRwqCxNiGFAGWs9F3GnpaXvyCiEca6PabQavPmeaROy7XyV4Ko03VbIlKZQhrtjJxoi2cyhWKjEU+IvjxW9JGRGi8G6druInhwcepw6aVdt3vUOztrHzWjFBwpd12xM0jpKUEkcUiLJxOCcK8jCJ1OM2pexPpubtJhf8afgbbQXV2U26VuDFY2SbFUclpMucVtolR/lif6QliYWJmZmYldKNteZaiFOgrRTi/S68BNtM+ZAq3cilsFER8vOHitEiLUupChfQyxIt8VxjQO6VStFFicwLPkIgDuM8oTyIDKQZY/LX3fqCwJNeiVWMEMUdoTkFwCKklKOEwowcGhoNJlLQyzxUDWJuSwzIB5nO3P+zHQekRZWawjiv9AE4jh00yak9WAJBrBHoICQ15eKL/88t6NCYV5nbX+gnBJEGf6EZrYPwyW01GdeTonJ2LNynyCwvALJWmkKAx/ThI4NQrDj0eDna1yEaMWsl2rMDgdKpDEEPS+zzxYUhTcg8do45BNpCJAkqmFjoRgP2cqelmEPcc2F9Y4RSds+jFskwpClcXKltjy3sNAc00OQo8K067QBrf7OS5DxST20LAsl8t76GIEewhsGUeCwBIMLYjqZK/EWleC4OeTJAh08C0Zcf11GXZn+8rU6bMDr9mbnMuTEM4/CyzOUZI49O6cEiipc5R9XG6NIJHrq8S9VhvC7GoDmxS5tCLD4RHGtO/DAAeBqFxatY0t79ZF7L6CG1Yb5cM7FNG+Cc4HtB1eXrmIzSBEqCz1caGV6isIFH9b7KNXVUFC1dYRGmRHzpmqMrrou4n9tDojEvEnVm7Zu1uvD7UiNsfuuZWiw8ZQA7/81WdvWhh0p/+gcqiqDrxtxjyCNQ1Jcg1RHPuKaOO8i2NdEQQxmBvwQwNlCmzcP6FQ11moYzRDhXaLQ+A+he9zLmP3JQf1e11qz6Sy4WwJI8oF3zToEl+48REjxBAQAuzdmL+Gh8hP9pKlqIu94lR7QfTe6UdH5dBkG7fkW9W2LSOm/7yHYISxlz7/4osvPnnlrampl/Tr/PjJ3t7eySa8w1133Rq6y1Kc3Bgi3Nrd2hn04NcT5ZncDIPWLWXr7QblSRWXutcaA+AjXcTExIto2u3NsxXU4/No/Y9mQKg2he0fyI1HYWmKApq2Di18cRHMTuPIwaKEIBZisdjs7MqNvRcB8XNtdpkY/QVBM1FU+K2oomn12t2o2+r1HKY0Im45awmKkJ8gaolE9koQMMbzShAIKwhLel8GbCaGLQd/AH78cerHMdbH7Jtjt5+crN0DQfAyZ6En2FtsFIMRHOuBGTpmY+D0AgDnTMPt4EJdk/owWHYcow5gmRx3bGJihsOSDKYgDnXsvhCEYoAgUlcb5c+RZUnDzMVH5ZePb/jh5aGlPmatU8pHWQBKLtcUBgMg50TiTBoJMtLSKKXUMi5fBv7S559/8ePp1BRJ4vRUVBrS6Z2cLKys3XXPCuMLyM7Q/sI9ljSYIfNkGx7CwiDRngR6rjK7VZjVvGLxZTBkoNOxhw7f5sSN2eXyxB51B2dHMywh4hPhwktbFYrKPfRuWjMXyTIXPR03TpsmJyIJ/6dYvhGAj2yCMFVtueKvSsyICdTznmkhMTA13TcSUSsD8MoXpwA0MTX1HBN4sxziY2uxN8cWeHmtzPjao2/6MXgH+tVfc5yIWzyE5ojK7UfbxXzJdVodYTSZhg+DJWi+jFp8Zu8GYY2t3LgxM5LBG7t/SHHbFS2pJnV2ChFCipfwCQVLGhAwRvjwxYvZGEC1xoviCiLIBGatHkKC275mF/xupbKFouOkc2K+HPuTNZxkOhGevjLiqXA6+O18CU4CgCLkNPhdIV6+a3OBjy2E3mTlN8uf7XMrg5bCFbjpO+xp4KSI3uQQujpbLAw+y2pEIPgFW35ZBP/O70EdoxkQ9nUov4ezGGI46K3r6WMJPcZJEXizcMhbnmLmg+OPLlSxyytSCTHG5mM4HvMgQXBjMal1Kb7JkA7nikkUu4i9nwufC6MuN0MtwGjV6+413PXY6yQJVBo/qhXgdz398srmbQv0lt0VCvkLQiXe+v2woKfQ+XA06UXHuc6LYfkK3/yxKI1lTC3Mf4Ci8mOoYJCVsZ3dHbGNzaJXU0f01QVWclzR5+n5MOiHP370ginEjoV/EGlgOCABFkEYX3Tw5uW1M8JNRID+WFQ+RUZLGk6djCK/Xla+hcbEF69dVRpjaB2tvfmmEASStR+yMxiJN3besLcANPLUkBjYuDqwDWHbm3IWpfDRjIjtgy/3ZeD0sc1dsY+RmqwnqHrszOmnrOpa0qDqjRe9//nxixgY+/CDfoUyYxWEdwGYT1NZHOzF2crW65GrXgYMvXKLDFsvwwgV4p+/HmISoZO9cuiuTRJESAgiFLK4a0VhmMEewqgtS91zmg1pOUO1j43B37lePAgcf0Chnh9yf4YdEbaBQRS1i5FmKIkJu05HeKzANOA//FD5B/yXF2CYEdY8pcBsQ9xujsCbUVt+HsJkAI7O0YMf/YklVMCRqJXBF3xhpXzPyWcr1OMgQYyxUNnC4KML/cOeBss6Ic3mz2BknJdvuR8JfnxhS4MeZl0KGYk4qk4KnJfsOclFwR8fqwbEMpdHEWH6EdIQswnCDP0KakMYDPaFOnrlloXBKDWAzM0y3xwbK6/BWuHwEreOLaz4MIzcGtKAJQ1GrLOGvhLMoEmGTjE88NGLsUAGflDpf+rLnONLHp1j/jaIQfmkeTaIGGLQPxIOwhCEDtzWFEMb4Vm+eT2S4ZwE0VQ9eRJEss98Riu3XCuD36gvehgsBB3EqFfKqUlRvsvKoAvUMOUPSxqMZzaVEcwQtNt3cE6aMKmu9RSxGWlozEontawZTEEoDgPm26IpTIZ8R35iqU2eINmRn1hqR8moipVb1XNuZxjlcEN3McITx2qbaTsDD/QQdgb/F0JdCWYwRWnKKpgB8Fn7fe00mMWoWjIXQwx6aQfn/BaCMdjq4yAUhckAKdBavRbJgqRAdceRkAXD1KkD/wFZ2Bk0zG/Z3cMUAhm4LZA9mMFMxgDbNRmsEcd2Bl8t6yvXT4M5q/Lig8eq0rIIAmBBUDdybiYCwG6W+Wi0/2WtXrWbj5ZgJMnodF1so92bzGuGP5uG/xn+MkNs3mCQFLcPUphUZJlgoBjJ0OxV0R1qO4KhK4xzIQHeOiPjyA1iCE7D/wx/B8MghbyZXf0+/sqDxoDFicJkEODeG6OMGYaFITgN/woGycH+AWm4PoOiuB0UEgOVjv9WR5xAFDYGswFkZwhOw/8MfzeDpADe5H8K9/zP8N9i6O9K9Nj9t74ZGoWZkBV33WxnIPxlhpn/GX5nr4xWG4ShMFzXskk7pNWZzXVQaIkgkhcQ9hLxNq/mlbDn3ElIijF1LI6yIOeLXpzg/3EiB/2T4dPHoBR5Q8uybJqmNGsAlRS3yTcPaAje0HgYQAEG+Rgz1IpKc9ScNa9DGBoWZ5AzRQFWa4HJ11V9jR9N2qZGw/IMK6INGmuSTF4JXEOEhuUZVoQWBZNJJzyZvaiboWGJhhVhjJnoD7+Yy5gKDXMN8P7/vYcJA7ApmJU3aTuOBl/DOYAe/A3ANsrLMXREYUOBaIuGRRoQBEF+wXN08ifbocExxI9tm3gT3ClO6cGf/dcODWNDxjnvey5EK9oBXdfZFdRyRwgOiC6sU6yzj8McRfqEBtsQZ6KXcC6skegU1wpqsyMknCchnWKdkZd5inc0WIaYEN4D6gvRCmcArMrsCIAnIZ0ie8snFHu49LpBCgrH4MTNMpdjuG8PbhP37eGbuet5bSKIworgScpzXEFKRDwp4qEJIQRaTdMuBiU/NkogIRoCIYl4aWhEkqwgKILQICo9KKggSMWbJy+CB/8EUQ/+CeIf4fdmM85uJrupFsFvYzL7mvl4M/Pty8bSb3SFCEhiArRMRbAmFv7pPGjMZ4Cijh2bpSkyz00KkyEaJsP/ngPhMT8HtRZaEFAEMCUITw7q7NnkQ4OPhehR7HkeSB690a4YZlGQR+KDimouYS2GM1Sy2SJPpg92NmurzpEM9sbGeSFbZg76Sp+fg7XVdUUwhxFCc3PQoN3ngM+LYyyIy1ADHiwJPgCtCNniQ4FPJBaMHBT9XudBUxGNT9Zc/2qSuZqHeBQmBU3DF/FThDCI5ba0WS3YpFBZy8nQWmoOAzb+0n0N7D4H6nlGsMMtnyedDMVrgxCGPc7DcSkIrQhVJ1gT5g3l4vRHhs4BlIep74m6Q0SX/moeTC2NpQ9fFMMBjCJIQbuBj8JkCOxbViYP2ZyK5M5HMtjt0wobe8lBDM8q1MnDoKUi8Z7BsNd5OHQEDPIjQ0JKAi+sCQ0tienbyoVgDpaDhxAs4qcnOv171apj/d08KChBAKNwhn0YRZCC92AsrK+XyoJMDBrD1rDG9qqKYjYDFh+m+Xa2JBWhvHgLG8VieRWKKBKFMnDf1bKt+oYhgkHrodVwB1t1GATXfuth2HMH3Vr8bLw7j8FEdA77waAEoe4i1AeHFoP+xHjm1wMEEcwhs40NLthUK4bXvlNd3LH+ah5mCkKEM+w7ejxAwYZh6kIuT/+JpVXT15eiMBkA1IcLJFHE5c7LX4HfapYkynBFB18Yw7onA6H78ol+0ojOoR6P1wQxRsO4VxDUK0k/0EE0wx8L4uBxnyDUF41ZnxovApAhFkQgB9E5ERNp8hATnR2KQPQoTEFs0h8IQhbs1VKBX5YpgBEX3GGtxi91ikhig7cgSq3leNNTG2wIrfHGVXYhlytUWBErCM1muMU/TJVyp9fPUxFFRdu1mObRUTmM4vEhUaMVH3ZJeMbAXbaGteqtxNClAeQSxhD9N5E4AggVhCwQph5emF86TUGA0hGLTafqCKK0Q82djuP7Y98nRg7zRqG6Sr+JsWkZFSoI3oSuVFGu2uWAqd8QkzhQXtyNiCQKp3KplPQfzJ+HiyKMa0UO9vF2jkPtCjxY26HDKJxqi1Rb902RHyLYisihwSWgFmf0qCe9w4eQBQYBJFwUkIQVzkCd6gM2rL9GE1z0B4Q/ECKIiQ4gCm5NacL/pQNRWUimBNHZfgqT1sWqReltx6pu9y2f09L3m2JX82B4pkAQSXau8kOECELGaFnWhezyik0VKML2XZsNqIB9ghsDsqCIkUdhMJDnprkCO+YLN7DyRexRJJ+gk/zKMsLszCs1N4shD7c6vGkZfdc9t1bTQEyfhOfARp9biUStB2dgSyQSdcJTgxpo9VrKIzaMIY219tAXxIgINMkQhNaDXGqtBKy9r0rIJr9FYiGQQ2y702ymnUw/JppvnY7TIR/evvt6l/Vh0Zx5MP0pTp6EIrpkumgagoh5FDksg3QaPV1GxcbiaKD6krzAEj1yIQ6PwmAA2KZ0dbLxSCUFB1Nu2NDJimfCmp1Yl85gwLvL6FuQ25+lKjib6Y+oWuE5DBM1rH6LaMTGwJs4k40aGtIfFvLoUQhDmvex6HeaVbVzXSYyMFcQrAKtiSDwDkMQyMFabD7OUD/T3yGy3tOJjh41C8K9ff3u7du3Y1ISofNgTtwoCUzfU4rZghBMUeRLsgTL0fyp0zYWZl3XaZfns55IwI474WK+h0YSwi+IAvct+wRRlIVDxrN8BsxgEJCAJ6YLU4KIrhCaISgIQQOUAr8gxkS9RJK0IAyGiw/4umdce4oWXrDsnYjAbiuEFoQnEfnzCa4uBEcR62feO9s7UhBVgtGahvXp55dPr+5+e/fjyYe58xBcfDcJ1I1biHBBYBWEyMNwtAKLdZTuPClgFhMjWXpHLTzhLDyJG7ALL8NUs5Rfwuqz66rNG0yUYCNegEEhwZA3FTaM/FKJLqDvm/zSKm0sLXEpSV1YbefahbLpARqeQy25ycMf1zeTLWFJH9hWEnnDBrSOf7QFv+AQBmyJnKFzzaqToYtY73OEOpCeEehXnTQJDvgFcUkKwlOBrhCAuoW8yoeCkoMhCMu5mLEsYkGIKsUcohOCJnj25fnnH99uvvv25VOkIExnq24SGBiXV7ggViCBCqoEX8mrfB+gu0ACYsRXG19l0YJ4wzsKSKvKvI0TXn1Yr0JkHLqRgmDWQ4dxH32F7AtzU5wItYUX0D6/+wqBlXepkWR0+WRLakTQmCMw+YMqQiR1kXctOCfvEfp0Dk/nOHBtdqBJ1/AUEMSVy5fVbzvV106pB3kwtBpw4L0zKwQ5xHCk0zs/qNkhNXLr7ivI4d1H6QMuIudBz9XI3eqyU3KrPkGj13XFvAqB1RP5pQI24ltakx7bCiyugWjBZtfFlcZTHZ5EFqWAxMrLl29sbt9H6A6XiMrDly8fprj9KHQ5J31fv3xT8XiozFIorZXagf+6FHMEMYIxsKDueLPm0mgT5YK4KNRJNMbj+gDbeqI9m4F3sEANOFO9hzqAxrYMPNWBX5xd309jRRR+0sQXM4xlacWLEX+yGnWXTW2UtiBkpUSBxDRpU2mQLa0l6rZANoWa2LDgEmuUdfcBkw3RSFzX+EBiXBOh+7Am8OADiVl98K/xOzMd5rZz75T4QdvLafsxc+e7Z36dO7PdYsBBqk0QAAQhy7ytytBSaOJ7gocguh6nTAaos0naSKELGndl/P7av9W9B68t2y8MCV4vZb5YvCBw/kIbsnZBkGtHgRGwduACLmsFWpgd/TfJUqHF2S2J2MDyiWFGuDJHC63Sjvwvi0Wm5ELBOzINngz6u1v4bpLFmhvPQRnwFLGW+q/LkobsydZa1cXmJp1p9Li4VLfex9VgwC4WjHaxwDrU2MgQf5FhxsMQF4aupbNXDUGoRgQOWyJlmhaMazdxfKxaHI965IKntoVDwG/8B85cuP/Nve/u/XWPEeznof7FhfMaOG7FSiuDQbFAW/BNkR5iVIMPuVw0LjMunPBilS62kiURyc9pG54rI1u03uqIME3O0SYbWOea3hqzZCMsv3vl0x28xhiff+EtdIGHpoaSbBLDVUzDfiJQ/Cj/Qr1Ar1npaRehg2y9XvoCpjrzFcTVLuxyOkPbEE1T+aPEWUTseppyGdQnumY9BSErAvlM1Yer0SCVIHGPnkgVA6YgAP6Dz5LZ9wNrH9y6c4rzUIEEbLjkIYigi2ILHp6zsSsjYdo5Zi7MNGgBbs5WC3XOKotPLXJJYTAIJHdP1oYeOdm7TZl21D6u3gzhXfcy45epozFCQ1qYExtqG5fosqWBZ55SKKlqlJSg92n0ZsjTfnVXz96MzJJXQOFfhyFxYpg+MaSEoWv67E0lCMHwbqse3tNOQvoHUsP3vbi9CkJo3jZDN9M95J0LzyXolvcf3Ak8+OvW/WXrmZR9AQDzT9lCHZM4GIpZDXCiWK2gIlnEe9xTECFQSAaUxi4VGP90Tl7cOknp5lrcvIQrraAoDAaJKzuiSL9M6mzAOZBpSxl8GbA1s9rXB33gt3gYepjHL7YFoJ6rRoc0VNOi7NFkUECjQpiy3J9hhraxyA8SrkIZg3cjZLjubbjJ+dXBaZcgQhCEggqTwUNCGI8B0gPdUSXUQILo6X+9PRfGsIt6uvOANgn46dvXgA86nYc6pLDKmsjKGkKjkj5f6SCI5A6Vxe7OnN4XQj1zuRa3WIy7ZEuERDgWaw9oSMZiyVMVZ3hSfXAUvR14iREatYzRX6cUhN5qi7WCV6urVgZ2c/Bmgl3HDiepKBV7nH11YphpM9yN0g4okXZBoI2gHYRUgm47kGNwnB7gGA9Hoc/Ihc/kSQB6IEXcuU8vn1jOg4EVjCC1oeopiJCiUK6BsKNXfldPcA0AOQpNYTCQkC1rAiurhcGNUYxaojPM0XMVQ1auRoQ9DcBqdgXz3CvZgGt4jabmLq0UbAxx7CaRYCyKx484pGe7oUsLIkSC0IAW9PiTqkeOB3qcoANNQBr9DgHOovc57zNprrn6yWsSD5bFy5rlPLSVxOr5ksea2XZBEMJXbu9ufDnpuYxsIZNOZ6uWwrAvyqzzdlqGYYxZxLD3RvINbByDNu87whobevONy+EODKVzaufsOlPNipeaWKlYJPUbdpT4MUFr1uOAprPuNg0RT0PUFMT3bk0QdDXSD6fwcTBYdOAhAJIEWhOmIIyhI711ZRP3Hwhd2M+ke9Iik2HmxSreswvCvqhv56vTlQhNoO2dGdxiepN21h19lTBMA57Ud704LwM2psI2hiyVe4Z2zpbRMDJmBg4jK7xExT8N2DUA+Eo8RcQX71oMM12GIFS3UulCq6EflUQIH8xBEMcteMj7+uZGQdxXgrj1l3j+5lSXFsdX68by24r/dILgxmZmpxGEyQGYGdMM9hMxSYNTyQ25hypGPcdkBM9b8zR8OT/pnwZMeV+qMjVbX23GzKysCv4s3rSlIf7rKwK/JdQOJD6Gr35LdLUJQvUsAXrV0sASAU4PhTSFGo3PnH40JY73j4Ff6GEKQsJc8fyDE0G8JhSxb7s49W62J24yW+D26W8wCArmpUo/CyApDAaVfMND6HcIFobWf7MhxjWT1Mj8lMTBoIf5y2FIZQq6GPNjoKCYVVbJpNGCqIpgGfHCWT2DEECSS9aahuiP169HEkzBz8A1w8NNhvdcSjgWQC/zeyzW4ACkhr4Qagz4h/6DgwN6d3//g/3jg4fsZaG18cQtJQj4CBw/sORCO3cZEkKonD+XLiizx9B1kCj6iKJNleaGMNqqKXwYNJNmbCU3GcwToQa5NqhBE9tA9yfMLiKMKszCY0kRmTXqw0AnoIB57nPCT2SFi0ifO78KLwGsBChaxpqG00MLQjI8+/2AUkOPaDdCCj09eNPBTwjeoa/hiIV8Do4OIBPoYf+XXw56X/dJg1nX7itBYMvCW1BF4DS5yEifSKjTSclUXewWQaiL2N447FycLUyaUWfMyqA/rLbSnftcbDmMfuw8RWRSoP7wGBtCYJ4PQxpjcBUMyaTxoEk5+AOapS1gsBYhM2kKnqmeNg34MQz+gnhuAKXcTzUCob8XxI5TdEJ9skvYCDWCYugBYjg6OoKX2P/lAPAShM/uDmhWKk1AEbfu2MsCDxlPmlGGwjnCpWzFc/pbMPQ9pigMUWqbp5PxZTBfeSu5D4Opm/DtlwlyjiNGI5dyP9j5MEXmeTDoeIgLq1TydXZJxkOUKJCKi8CZAP6y5OL/eQgwwENAEGg80qBTseiUCYdljDNQW1I4dOhBOIjeAygCbkLiTzMNvFUIGvdOBEHVx5otF2p+FP3vAFMovSRRp/ftgjCrrfjS9nZqNsraEmgmwvAxioq3j7d1lpS5J97Gl1tJJjcKDmPAcjh2UYbhzPsKIoOCX8SpaImY+kJGTF3gvIMg+OTlkSRzIWwYYjC4BSEZcGcE0PP0+vp6sXgo5HBYPHSCEESQgLdpeFIAggB8BWF2M5ThExKCwnLnM0kSKLmKUHa/m3UINwTRpyjM/xxXsYPTCQaYxenDoNWjGQ12Pwb9aRNvImIjJiKuRmUIjzcDTcfSFFy2nr4ANyEmvvEXTc6V6osXVmjytuCfi8l3RAzG/JBKRszPcFELQjKccZ7++OOP35d6OHRykAMcBd4KOQJq+kKMV2tFHP3pdyY99wcO3NOSWLOeSYmVlzItBZMmQWSZhhaEkNS44WQkZil2MJXappeobrK6miEGg069+8WUhT+DAvfylm8gZkZM0odlxMaCD0MWOoAqCBlSRkFqBKERhIJ43zcNQy8ojEqnYDEMh5UgJAMEcePGOn5JD2WnCD3g4ZAgBuAcCDcgBvyitXlwBHyEx2cQhJELlxBMF8uXv2tq4hv7meSiHVlqu0hXyEEoTkMQ4y2J0J/KQwd5OphJIeTcLQdNYTCY8BqFtTLgI34MCM2L0UDV8AJGLnEw7JOGKgkhkEaMcRaTMCKwtERCqEIkiwUSxhe+ubiIcp4aGZu8OAoXkCQV+hmG5mEIa0EQg3ODAAdxCAdRBHqeLvYEseZwkGYwinAdssZArxN6eBF6IAS7zVyYfT5tIQSWv3uAXsaarSzkJ0sV13cTzUG6AvPsdgpJTej95F2figq/kMjPRoSvWGIa2uGbDHokRb/qrNkZjGpLk0hgL3zc4QE10PBEDE9+DGkRBcEr+K48ZFwGygTgGVZxWIXJMw0xFPJY0x+hjyu7tzaDEoRkkIJ4GoKQtUWxBy7BgSMPBlFW5WI5FJT1RT/1O4NHwY+A4EfdRi7as+7pM765d+tr/zOpP5lINL8wuykjiHlWk/gIgulah6sKI4KIcyDFKbAUnEYkv8HgDW6E0NsYuBeDHqi6zMJbtzHZEl5AAKY3gyzzL+o0/UIHXHgNCpmpQBIlmLK+uRh94S0Eao2K+9SnKEjL3/AGJ8OkFESTwVkX/qGI3x78UvPyxtNBEkSuDOTKjVywF3LohYfAYNWLaGYefVQOdlvOJLfdP/bEJ7ayUF9MbEIGqk24nTCqILcgwHCmhUI9bz+zzaJn4SbQpiRhxD00ZTAYO9maIpev/gymg3AxjM2JDdmB2A5m6P0ZKjL0gZ7SakhmUZmkHjxzMUn3DYy88DwwzBCKMTVGhst+huQLz7+pBCEYHAhgnQA5kCDQZvj9/SD2LQgWc42Ja9eu1YrlCae/F4AggqHgQPBFeIluIxfav/q0ItSh5UxqLKH4EimoQbQLI8wN7hLEGaI4IyiMQkOwGEUMzeDpLOeIJXT3JRWFweAJM1eALwO3EcTmME618eVtCtz40pYGtXP2YklPAGf0/D3gmQZxX9n88/MXpzDkgQCdBTLwt3wNuNPkRBDdYHDgG+AhRDfDQZuBBPF0sLu7LwfngJ8PP/ywlsthUGKAhrPLwXIQODp63cyFvRGhYT+TzaPEM5sUKL4dZcJJzLTdm6EFAR8z0UKhKw2SQOpsilFEaZQhoNQYqeSPGww+AREGuIXBPt06tqPC87ZYB4ZqqaTaUzoEuaRCiLxz8aa8FSEmO7YY9yDDpK8BXd8FwfBwk+FwvQgcwjngh+qP33//3QmOd4cghlw5B0HkJ3LQiuMMoNtRdgY+enHgCILwPZPcp9/FlanzmRSx2826XwpiunW4UTH4UchjSICcQ4QjjlB7iFMWZ3wJa1zkE0wjkZ9OpZbiKgl2SQEz+Xw82pqvCEygvLLx+dzczqfJTmmoZtOIJqy0BnSsYIGLgPzDVxBhEbwpb1Z6/g1Z/pfbDOoTb2pBjBNDeb2Yw2BUDoWOpuM69HBjHdV6qDzeVy5iKqNGLcti2aExql4nV0Z1AUGgyjByYRs41profHHKY3GDYoJeCRHWojTFIHdpclPo4qKY4pssevXs4CDVHXEow+1CFIXBQNADWksnxtmmaTOvLDYG3vz4dv4kWVFoXJgirCMDvVTVEjJp5REwEy4t5zLclwEd2zH0Z9+e2qV7zt55dUEYFmAY9jQMv7orBdEnvPVjjXKt0fiwVoYiUG9QDzQYygWdHGqMcmPiTCNXzOVqaEjgz5wIki6XUWV8NG7kwmyBSaO557S9LBS2RUWB3gGQN6pvtyDAoJshrn83S/GDkauDiB3k7NfBq+40aAqTAd8UBbe9qVu0PEVakKZpYbAyRJH6JqY5a7nhWmfHyoDBGAqHWSEJXKromJmmaYX7MYwhPovhRiXgHZbErYUuw0a74TajP5QgBMN44w9qOaJ+EHr4eT0UggZqsD15DRqALugYfuSwBkfifOZg/AoYN3JhHaK788/aE0b1bTK4RTUrTnyeymHGGDC0CELLMPHVIALGEvEf8f3fBgev6/9gS4Qa0CIhRFCuKdnIhSmufMdsJ4bEJvmGaGKG/MT0icdLRRKJyJJShD0NVUzoZVdFZDFm9gIqZqYkfQeWuvBl2KUhr8nbC7tDnG3Q8e6rb3sZNhZ2txjDp8daBXENqMFDlFHg8ANFPD4k22PkHMoNHElBlGuHQDmHR9BLEKYK9B/frO7tLePPQMfzoBF4Jiov1W28Wj0EGLzHAOIUOsiA6F0KLlVwU5gMVJykQR6hFsC0LL0IuQpoCy2A6Ca5LjvD9MkwWGIbBJJTdXtnJIGdga2IiJBqoQLHIIs/cP4cXAWvo03J8W7Bj4E6tjGZT7qNjLEYJt4nvQ1hMnRpQYDhGqFRK4+XG1R/NPpQgVzLPzmRE35CobZOjqH2YY1As+ITRi58mhAB0sO/e3t71SfW1qrL/ufBCIqdlf3Ozdnp6eko04z+gjD+/3WKErt7l8LFfo22tPgtiSAVxqWHX2Ic4oApRe2ZWdmCmJEXvYVhhvxKYmkTXkKoiwvOPFoRm/ASLAKCToKoU0hUhZazSHMZGiPDZAqXYMqIgCpfhi30YDauxMRdZjthxvwNX5KBtwkC/cprjWIoJFoJ5TNPPjmey4/DOxS1HMhDoMaoLS3Vauh2NMYnGmeMXHjOZbDlRz55gq/t/f3v3t/3vn5k7xHLxWns8pzXte52a1m7BdFtUuh/H4EWdDAhWYw+gsmAAS2xhMJZqhzydDkn6CgukxLB9b9pZ0CtkJCNH/m1uOSENhTBM/+xd3WtzRRRGG+UXghxW6NV28ZvrIgf8WNBYvxCs7nQeCEklC2RrjGRqk0TJCYRQ1qp2GJN3lD70iIWFGMRGpE22BIvKlSLoihtsS3olYL+Cp8zs9tJM8msIt55tJvdk+R5d/c8OTN7zpkZt3MYQ12MNmEPXyxQrpsVReRwDBmnEU25nggYkWrLmzyX1aa4v1Oh2QiX2ggtRKfTQy1EoVqtNKTUstIj/RaO59rEmo3spTMgxCTkj7lQK6ywRRszBo4ajWL25ChfOMSEAId9J9cp74PmkTv6XLiLkD1EvwTRQUy9ubW5VTHEyXV0KmUEil+wgJYWxeNqAMPd+NDI5dGagYeWKD2wmB4VwjL/UCS4zJ934WgYJjaJmdFlDqdEoAIZMjrSmrA84wJVQBBPctpr90yw6oieCBinzgz+/iNnKZSPuys+fOQM4RKHEJYFBrSs1nAIAlaU0unBNO9HJPAfl7QV2SuBEJHM5Nwff8wNZ8LSVcgy8Ml1RIjGehmbNWo2jg4/Ud+HturqCGWu4/xJPpAw29mmIIQQdYBIUxIiwcbFkuECzJRBFtmCGmNla449eyPwUdYzOG/65AygGK2WCTPBB2cH1YQQBTIaLI+j9gKZeXtKISXC4w89JUa2qhTthAAAxSFSafAhHA6F8QdGeK8JzVolEIIYYUtpDw15JoMN8SGTkQkhl5tly4WjajFbbuzkwYUvcp882ygf9V1UXUW7Tzfjppxh6uIhgCB1QzTxItGio1MpI8CCcRoTG8Cm3UPURqOGUZM9hIwAChAhDPqeKQhBUVPSc0IoEVD8EGOThvDNNI5QIAMPce90XqPZZfLIhSsQ/oEIQnCEq2cHR6xUK8wlBBm5pmQTQnDCAiEgYIKt93Y7B62azCazTu1btgEpNg6rO7nG4XuffXN6+eMXG41yNltWXkX3jEIgGDe6eogr3W+EXHEtILohkNF1/6h/hpx9nWxq+MER/Lb9fjJlc5RCGgqE6GiNwuW1yAx28N0KcaRJANGIf3SLM0qJQJbXBlANg/8X+NwWjBUFZ5oMmv9EiSAlkNoUSkIMDV4D/xAKhSFs07LYAwbrUoZK/LmzhOQ46MA7EaDEHyCEfA6fVKtZOIGiPe6/XAAhqqDAyVHy4rc//3768+nXOGo0Lnq6XYUc0DTMYCKeiaDiaWaUzZ8mpBshpBHHcjdX7lRKCDygpXvqfh7Qqvnh+mlreJqkiiP46d9SI2BELTJqfhKdDnRwBADYQOB5osB0OYd54kCB6qMWUHtP5VG0zfEpT2I0tdC0AkG6YlkhE8JGGEynU/0j/d7WYNjLGDHSSjmEaA0hWIX9NF6sFhhRcnqVc991OYdkY+fybDJ5Uq56mBRzjBDwFLmfv//+59PffvvsCiJEVnUVvLmIEAk6RbfNrCKEfMGiX9KmUN7KwH3+xQANZNHPAlpBkMOg7AQKbjb995lqYxhseLW+tdk0PeZ9/k0O0MR4qa0tOLoE0crNnKwERnthap7NFxODJs+mjcm/MFUAOTChgQqBBrGOibyYQiERAuaHscOtVPqaa64ZGQ5d47UQm2KEKFEO9FOeHKcCGupEcBfRjRAD2lr19YvFX9aLxWrWw6RabZQPiRCN8i8/f3/6xW8/Z7O5RuGTHlchOn2B0W4SdfcQQuTBFULcjFFBQCt4FtBi36QdRgN98b77mm4IAEDcw/m4aQM0jTN0Q4ngVMOwsOTAGCMCZAo7887EIQsqhCk758FH/rFZW3squhICglAknjtnKcc52ypRwBp+4VMuFz79lBGCfASEKCETolzM7sD21R8/TxbL7Eq+3P1ghWafO4FT+P63H7//8cfP0F707WiK+2DbzXYPMzXQIJ7QzUDA3UOIOSYmJiZeG8938ZAPPf3Y888//OCzvW6lcxbNzoCWx2D7XEUNhhqBzH/nVr1ewet9dRsAPGhCRa+6OwL8AuwfG1uglwJXjTFVjFQL+d4IbGGFuyeIFDyeqVJMzMuEgEugVgICboAP6QQIASX4ANeALV4/BSFKFJkiaU1+dZl0FX19J9QeNF4/3GkcOcN8v6wWc9XkSWPnoy9++f3HH7857Ese5fvKivvAjRfFU7xusA5egmkzo6KcQUmInJMjRKSPw4lCBCxtwOVBpTEgwUUR0LKlYmsQDFebU3yaBK6Gi7HlqDZNdwRIAYZn0pbtnLZV45oCIUaLrmhUToPhLQUyf1cF3SymOE+IEauEcJTNB7yAEwkiBDEBDQXRglGCRm3slSCRUqtUGpYJUc4hEAlCZEEL1omg4f9rrF9ZLcJJ5D76/otqtghFrhchhO0S9JjGmRHnLoP17N09RIFyhLHxMaJFTOBpxIcnaZmbBx99jJa+UBoDogWbmwhoBTxCjMoWVHWHIS4IZnORbF8xBIC+xehQ97gjcJnHHIQLvB5CtPwLC7GpvAphig0K1gp5ANCISJ4LgSLXQ3GeENdYc0wmKcFFuS08VyRKKV5FZUFYlxItBpPSXgt0oGBF51Voa8XkIZxEoe8kmS0OOAO2vmSdyg8OG5Ak/kCZw6LqPnCLm6M2ESKj8UAgoNf5sebmIRDjt2sFCtgbb28w7kfJOapMIU+BEg+6GQMPukGzY0CfCZXQuCIYAUN6eg5onn+AgDW35jvOIUfLcCkpNcFsfg9okWNjhtsUBUchPjEORQchJufQSwQFsIOAgxXyIk718uzsHtKfvHM5ucfH74AUyIAPoqsxOCwRAg+dyYv5XF+5UDgqF9kc12wgxm7+KN8oVxsQbPJ9jcNktap1vw+asLgfT/HRWm3GP+qIfXPVHmKMVhPQpmJjBZYRzLWR6FFaFUV75ulHH/LcD0Y8rjSGYde3RE1hywhTvZUx/tbvO5BZpgKKRFsORSeEt/gEnpo7gtuaW1p3hHke8CZB1hwz5zPFWC+FhrB4p4egHqQFhwDbo7GAUOktXEUJgpgDhPLjQyAECbJbViotE8KDB4qTYqNvp1pNau1TQ+we5U7Wq8XyyZflZLZQLlO4SlP3IajvwAVNRS0ajSKF0K2YQXJTNOV9foKvMpHjA4Yd2CdRYsxXwXjQ8zhKjVXGCCxLxSxBRyFKflUICenTWsRRLQfcKeW65lZPBHBBg9kX8i/YyY8pHul+rVMRcxTTnR7C6k+DELMgBHkKNBGl0LCVJtvjgYII8dsfSIi+NHQ16aCaBHsGuxCiisDU0eH62sHBbnLAczbu+8/GSRXO4SSJ1gJPnSTFvMpTajzYF6/rAQT1opwf9Tbb9iIEX0UihoufsKe8F43QQ1RViDLjx15ENSGVGisedQKsvsU0gxFihKhvCZpmIood0/WhMUN2z8SRAXfKILRlQojHmZcwmUaF4L7mVq9zGOOpr9xZgmwcigKbPWChXeF8AkeCECRWyfKmJ6kTmaauhIUia1i81e+1rd8iJzF3+t5L8BJgCAnYM9jtKjSa6NyeY+w6OmbdiB3qOthRy8OdPrzuDPS4D6LNiPib3BD+GutT+kXgWhGHwNXyhUa0aUoO4ehMnuBLHTyIvgSbGO5WhTFgu7goZtFtigRFOZU61sk/Y/I9hz8R0IGXZGQYgBJBrLmlKdbc6uUh7kXeA/cgP4ENjkiR76KYYgrJQ4SoK2mVOCEgqRJxYraU9g5TwRRYwAIPp6fvITvewp7wEPJViGHe9ry12QNwowH5MputVsvVcuNkZ3V11+MWbwUhIrquB4Nx/0wFwraJoOkSmCLu21Pe8x8D45BDCE1MlP8MJ8SAfBK8dcjw+haT1bpAlSFemCiTQalOwqFLTwQqigl4zEw0brBaKyI2wwlGIhhrGicAFYL7mlsF0KUHwguUDYshC+JM6j1PigWFIgYEQYh4xLKQ/bYuXEhZaC9Ks1SHf3w8W0JHod87TLNFwDGcnrYQrZodOiPEcLer2OF8wGRSq7u2Ha5DNILyGV82SLK02X1gNdnrPiBg3IzWFv29pNY+1EfuQ+BmaFhXYiGHHXD/3gnRK3mVSo2xiMVTr9gT5XMIud3i5VEG70XovNhFQ6UTX3IAlmbFLiQ9EUAZnTbkJwjA5JjY5VW7y6iwUSH8zTW3uiNgHv0YHyVO2RDch4F7VYp5bDoIsZcKh9O3b9yIiuqNq9PbPt+v+z7fMXqOc3Ph9KAXU0y99B7owHjQkxBXOP5hdRWzi618yaOVEC25+sDu7uoq6zyUq31FNrGt1uM+mH4XaaoI4cmLKe+nBzw0QtoRPiXgM2Ki/Id7/zrtSeMziRmetQzyTQSPvqRGkOSmgPL3jSUI8JWbZqKj2AkwfzDDRxMuR+1hhQGFh1CsuTUlrbklI4zde/0UAhbjUzlYne4DFC90V+SZQpMIYaVWlq692hca9A1/NPjyhe1f4S6Q85qcs1Kh/hEvhv0OtzgPVE3GRZpWjASUIKMfVB0nQccH1Is4wVvMhVzXy0Mg1kuyVakHdSbBYIVpNhf5O0GVh6AkoIaYP3KEOcoQT7V1O96nZVHeRfH5+08RMd7t/et0ahdYXYzBCmR4XQMMm+kokJERnHoIfJd/j46MM8yMqIfojiCvuTUvr7kllliSEWiUuB2Lwd4UHzc83l0xz6b9PkeIzN7eheOlpVSr5btqaOlq38bS0j48xNIxSiLIS7TCact7TX/6PCFmuzxl7MI7gBGggtOPWLMdfPkAqtU/T0AI+0m0+32ABDYR8HfMzjWcCTCBoeN1UVMR4gUaKW3H4BD31wQMtRkf3o+JdJ6i4ZV3va25EaLG4qVGOyEy6NtwQphKc/IhY07FVI0XyNRph2FqmoIQ/3jNLRkhN00pj3GWBhljPFApxhmCIERpz3ruQmvtuaGrLqxctfHBen//Dfv7K5bF+pIlC38gBfoYghCkkgnB4lDcAWBjS/JsQvxdevfggKsP8j0JwZ40FnlCyEkXQjYZIzyb5CJUhPDEKCPoDIstnKvgomkBX8XOs+/QKhgKY+AnTXbbysxw64smoxkhelTAE6U5a7wIJm5GR/0Go4dh12kG9RpvhnQ1IdArfI2tuTWOpl7T2Mox2Ig1t/DefE+E/ELHwgq9FfAPMiFSz634fCs39q9/sLLiDW/4tle20YmgqimS9GwJDHAnRPYBWXbbggdF54kUHc5e90Fz2o2KGFoBp1BhxQV2gxJQEkKLUUZweppda0cRxJtsfsC3afuqyhgRFLNovJjFxIHfoMfeKBXGQGoGTTevNmeTAGp+koxHp/Fj9B0CIKl7MsBUI/DKuTFpzS1tgTTX8zW3FAjzWJRoekFkfLsppqFAL6KTEGmks7a3fdtWOuRd2g55b/yg/7Z17803Hm+TlyDrpxkHBCFor0tgqrwqE2LtXH3CxeQu1mM72FF4ay71swyzjmRzHVdu56BZ7lkihFdAtGUEYzlp9Pa7IAPJh48LCBmBTBjxaJXNWiRA+/QvxsmOgWathuxnhfY9KgR8qYlaChAq7tGo2IoBwNGAJEiWmoSpQPjHa27JCP9A2gnh5YRAvGF/G/X44f31kZGR25Z8vv2V/tAg3mFOQkjasndKVkg+hx2ZEV9Kw+kHBpRX4YzBWzyrotPsrkVT52UKuooQHKMwPkarbnQW1JE89MbH77z7eM9bCXGKWTzn6lsMvIIFTvGLR41AABV+8hrtcoBFnZJb+Kuh/VMiOGtu8ZPOta+5Ra+8f5RXIahrKpWEQIAaTxn7+2sj6Rv3l0agueXmlQ9ADFDCSvG+xNykJOEu51CVGHFRJqT6KjTHRXTmCXXkjZvMQUiEEIXbgFCX38uTn3dFCCxSO6XrdWqigva/Dx7UdZ3Vt5hqBA6wpVMSfRM7Z21fM0BJ9EXsuJ+DtsAW2Cq8MEYdPzHL0BRUMajm1QjyLeiuEAiX2AhpSntv+I63va2lbd+1/Tffcbtv6Yf1H7w8iJlq9xJz5wghn8PAzkE7J1az0uQ6f/cq9EDnZQTvZCJs0YUQkB6T4Ys9NSEg54tZFvWzzq2ob3FHMNmn2WbLEBdgqyru5+C+5pYaQf4h0A2RvaZMCEScMinf9obVSu8PbSwNri+hydhYvyEUxrthOIkU60o4rOA7pVK4x1V8klzbPYD8dLC7c51sB/VVqOZvgdeA1DsQxNAOTYOXkTEEE+UfBoeQEcTyAVg9QJxIZVHUt7giGM3O+iqsZ2ITSndHcF9zyx2Bi9ZjmYdOBDFyC4OxnrswuzE3ebySGtrfTg2PXLURJjaMQMhN2O0G/gMZ2KYEQijOocv6En/XFvK3BFwFP9dOBBkC4t6PIuHpkN4IhmkGPEJslfG3EYxgpUl12+dXJGjWzb+JoFhzS0b49/dBEGKPSmBSK5PWr1fPpi7sH6e2V1Y4GfgWAzVIShDbR5CE/zNb9BbdkBA4hPc8hAzFj6X3AfE/wl/snUFq4zAUhuOZjBfZzFDFGizP1BBjQTC+QKB0G3KOIdBlmQt00RvMcvZa9RxZ9Aa9TfW/SMi2GtS0xZiiT4K2Ie+rnvVQHKqo1pCQAQWBD20env7+/0dHhjzdV3izwagcGN9pcC8Bbm094CVjGln0FL0n0w/oPXoP6BARNvz4FIbQdXAFcXeDDfaHR31reU/brH+zTVaaxWGHgmBCZH+O0B/D6ZZiO8JchA1WwaEgBu+wqJvW5cIgQga0dxocI47h/OuQGMMBH7nQS8Mt7a/W5w7e7bE4MCwOnO94KZRQqjSLxI199diOMBdhg1Fo1MWbKKPBGhJj2D7oOqD99thrr7fK6X2UWBsIVgqNUuqXYjk20hzRh0jwaWRhTiXaSKaWy6sluDL9NYjvnsHxcQaMZ+QxnH8dEmvgOR0wfHmJ/6fErq/Zfi8MSigUA9BfBeNHUCiBMYyVBSmyTS2l3GikbtQdNchfJpt/8Q3UPoHh3OuQWENtHC7SctLRBsYwXhZ0CA2e1loaYm0oDCvDzy5tNHQMKRlkrSMRTbhYCi4GoZXpVTOZLFBTNQyNEyC+0a2w+NFEEw3OQAUhnYEUiCaDc0w9ixk3hm5JN4VjZQW+IYmGriGVxmDnARQdnGFlO1FNKYsZr/O8RaSXwMnYinobDX1D2jc069OKynZ8X01qLmZcryx+JXvR1ZB1NAwMqTZgLgIGT9Eki1GzACcMYJ6368DvR3g0hA21WxRcsDcLvmExpSxmiySTQ+oBeR88lCyiYWD4Ok+/vYH5tLKIRCKRZ/bO57VpMIzjiOAPtBCzKCKK8y/wOkhYiZiAFzEnK8OR0xR6mDh5Ke40rx46pS0WBlkvwzJy8LBNUIoJgRV6G0kOgUAOOZX9ET7P28W3NR1ZZtUp+64sbzreD9/3fb/Nsx3ed0fQ5TO382vq0inhRBIuNNuVyuxiodlsFnqFZiGRvPijSX82qhHCbf5KfnEbl04JJ5HQ9jwvMB1HJqpkqERcEUXJAKmqJImSakkqSFskqlrAloTvE22WEc5O3bxyHBP8+VPCySNcmAowELFDIA8QBXGzbogrdcMQ4QYjYXWpLFWFlqrRTEirmtxkHq4JxzNx45Rw4ggXrl3zUAEZBEI06psQCUmTVlV8NFhWV1I1otGWBbmwLE2SJJXIsz8Sdf3qISa4g6/x4qmJbAK+sgnZHrj/gMD9CQ8Qh7ISdlordZBhN6YbtoEBgBxAqcBYQIuQRc2CTOz2eiqExY+idpLJ69fHppIbdXOoiWwC94sEBvkHCak+v9MD5gEDETYaEAOUK62Kbl1cUSWoGapKQLRIEA2b+JT42rNkOSwH0cXEQ2Iitf7csODkxM5PJoQbRyI8XUr+xQZ22tBL1ZlcBKahqfhHCCguRWCMiXrAegEEfEIoZc/d9zUSR+4gGptQPu6pJI6JTIakadbu124hUgIvXEbCZSSkh8GN0c01N/HDTGQTeDgJCK8lnt5Vq1v6VjEPIWUmv4e/RuAyCJOfh1sYCFDZxhj0Y9moQ6suqZpPIB5UYSxDK5ZlskpMWYNEvAkCT1nGTCKBmsieAn5vLWkyE5kE/gNX3MHGlkDvq0KR2ylmEbJ1TMLr4xD4X/TwujK4JOfqMk12Hi5PUcLVwAP13VAJ+wrROq5ru3VNjn0SKpEXBBG8qOIYkhFH2u5u7+JBIIDATGR5cPf4pMlMZBH0DwulaqkkcCV9G0tHbebRzM7HHISUcnnAbfxDB59UXi7nJeAxMXPH97A8j7vFqIUX7ORtpknOwxkgJIEI3dZ+vzNttzp23d7s2FoUKb5fxjigohjzoAxyYX3qFQKvjIG4eouZGLUw5h+S22vtlInxBEZZqta2S9szQnHr7nYRMbXig6K+kIOQ2ryWywM93IB9LOfvvKzkIqBgRV+kTGQSmAEU5vAJNtAA02Tn4dytJBBYMOx127an+7Jsu9NGjNXCj0DQuo/PhoemGZtwa5qm2v1kzUZR87BA2K+494+/PV7vfLk5Yug5bD9OdMRhbCxAP12HXyxnDjb51pYe1e7q2QQ2cjZ69namB7aYoLdJR/4lPSUg5ygqgwVlSnnIDkQFOhw051OZmtQ8sEB4YaNlu6h9WPJNN45JPAsFQoYSYYKSbzHcOs6q0e1aplM4JBDtPZf/DOf/foNXc7iEvll7foRhpHfFLOkbC7f5mkBZ1dJSUX+XRWBKDT97KodXbO5gCajYHZ9JQEBCmcMYJdQcy8mzQMzPP6HZBNLwFqTJzgMLhLLe6Pihorh+7LvwV4a7AgFwHGLixRwWBkIVja7ljAYCqOz41L02hGGQCGGogAh7LWZoYCJFYGLdStWqLgjVwZTiRX+XQWAjZ8lKf14OJ7B694zWbbyhRZwKq3ieUdDP9hxbiFweoOuoKsPgyc4DC0QYtvr7/X6/EXpB6DdeaTJxHMccJ3j7O2ln99pMEYXxO8ELpYxRFIpEyFWovPhRLCQ1vEGD9ca83rSlJLQEEkGsxUppU0VSQQoVrB+tjRW2vajQiJVF1K6VmrQBExSF+FpE0BgiNBr/CJ8zk3F2O+ls1dN8bJru0zMzv5w5M7PZra7XrFrCA8SgwGx3/JgBiPeLEoiht9wh4uaJftYSr4L+jXl2x6vXksDqg09ZKvv5WOwz+iL6m/4K5o8GM/ugRPK82xa/ABwP4+f1xauVgqknwiqn/PD3QZ2ZCv+TfuTTw1OqZvx90KvBUA+eLuPHk82bJyfHAAJzEpvoMQQOCwu4e2NEFRa1bLviBYKRDzuAYPvkpNHYPyzDxgHEkXKfsko8BgL9i8FkRXhxnn9jeiKLvebfeAMnsQl8FuNAmBS88To5MZ9NMR0Ms4KqLX7G2WURH2hTnj6EXUGBeVKRvJ5BmBWm6N/RXT7BSEVpM6OCJzqoPTw11BeIHzG2PCEUfqTHx/+8+ft18MCBgKGLSFQTN6oL1QWBA6132XZNA2Jgl0LCOM5W8szAEXUXQbw8dtfM8fvFIlA5MhRDO+VKgA18Fgt8+s5n0yMgKfYpIyCyBgWvjYW4bSWzI0L3Ss3J1D1NzTAFPyYfJpvMq9bwLYVSydPJXJkWIkwK4l9LDiZzee7ApH5uGl3BkELoLjANiCd//m3hxtnPhMXjzwKIk5sL1XU0PFi4Ub1BK+CRBNa1yKpkeJmwazoQRwAAQLw4uFkKHA2RlTkQyp555JiYOTQVQ49zAIIBC7LUOykeNWI+FaECdiqkbOLqEUK5schP1IwJCP68NqDMWAopMdnbI82x0mKE0Ye5h6XxXV8XJ0nUzKCggi1TOGjhSwPit4+wnP37jz//hjkHPEY2qrAEmj0CGOK0Ch5FSLAqllWh39MvarZ9EYhAuUwMlF8qNt5vbEognnEFrsDxeJD/2uezdbEUn438c5mcAMWM1Gcf+FSEKvGI4iE56n6PXSngy3aYus8VHuTuV1F44YWA2CSgctrn06iwjF0yAog16UjgAvDMP0KILZ2GvLwwUH8gFmgWEkj89hHHwSrEI1bBsgqFQiQeSSBpsBElbASKaATLXjVHA2KFgEDiMDgQeOtmozHO6Qjuq0ZYIWD4/fDwGUNFaAh/JqczAr0w8UaASmGOMezClXjmx7yfTYMC89TgGm8R/dPJTD5IhWUZF7AFW9QDhKkeclNrA5yIDP31Eg9V0q6m4OkmWb5nAcYo9mnn0pNAPIZxAzJHyh9/+/k6PywGK+EfggWrsEGP0SjY2LCx1Em3RAXRAkA87wViEDDUh8o093QEEhqN8lBw6JApHoZ6UYNHkmPfYKvqbZp5K3FwfsCvMeQeCoiU/kkxV6Ua+D3IDZPW7k+nvwKMLq31cL63CY0lLWb7KTD+vzlUS9iYVPH2igpsOYeTamMCHjHObchy8bDYF4hHP1pfl0nkbwvxSPSVeKFAh8mABgf3SAVHzeARx8cACtwAhO1IIO6WTqyUdnePeER4qUyhohHcCQ7+U/ZAWQIhNl5UxYCA9tliWsDTu85LFdReCohR7bSZBgUPOOKKakt5b3wwKahc8mHZmqiADLbTV/VB2jL/54sSCGgp81XIpyfRU5nsBeYFQij8/hFySBEiblxHMCigT9ggIArgwimAkEgcAaJKRNQ+qlqWQ31G5bZ/gLhHOKH8LO4gRuw0GsEX/6nW4yEvEIcD5ISm4EUgUBz0toEebHUF1ZzuHEI7rZ5BwaOxhoaEyc5bqRgUpE3Snhk5rUUv1BS2fynkTggsEoi0eyzp68Pag76W1oG4B0DwJLJajViJ2z5xDvacA6dgO5yHDQetH4nH4xYGmuDBrlYrjhEI2ZT7A4M3G5uD4tWfPxAGD8geQ4QIvRjePn5lu9VqfbzSdynd3BiyuhQQq2pXf6SY0kB8EDanOWD2gYnWlEDIFs3QjkrArxTico5rki49Qph9uByETM+WAn2AeJSPJfGz4dgHB87ewd7e3sHBzIzDmXBsdCAYWYAIAgIDjdp3VsRyAXGPDoQwdtI4oVK/yCMDt/EdTgbuO6oYugKjjqcewnlGhzsvDex///2LMrfQlmR0BaZkWEoCkcIFzMfGXL4ZFJRIHo34AP/JMG1ey6gAW+P7Zhbl63xGZAFXU1AQLakkZNLtxNyyn8LSA9JQgKXJqfRcbm15McAuXcsQCr9HkTWiwQsf7m3sIXPYKGyQHRx8d0BI0B0RIs6JeM2uJioztUjFqT2vAaHVGDtu7ARoeCF5+KH77g+y7wgYKgLGgq1r3JrfN9vtZkm941+VI9MTW6uhvra6upWcjo2ZFWQJ1jJUl5lJepzypuy+SKH7p90mmQJsjusoCX8fGGnwdhd7Z9yLqESK2Yccv8jo0tTcmjcD8gHiOgaSAoIPyTastwrAwrLQcxzMOLboPDAfUYM5Dg7HxxCjPxC6vdW4OXioAsTpyNdfH3Avh8or/Yuxsls6WgnguX4ugGjXH0KgaLKrA5EM+VrM/9NJF+kWTRrgXIjAHVjOzc3N5QK+UOaoLTM5T0LCP7FqtdTfhzUXQYu087KEkYvlfBTY0lRusT8Fqt/SgaiABoAgiaDZhw1OhGUffEc9h0NjzwiIsGv2bK2aqDnVqq0D0fdc6/s3G3L+AY/dlze/7vbgeKtfMVip3g6H283N7To44PZQ8xoBcVzaKd3OxLhz5a2Xdnf/FxBb/p1OXgTctPxoZ/Jr6SWOhmglk4JovszchTniZbEvtq7Y8U3xK8v3jP73knwRWKJXBgXD1LUalvePEBHOwYYw4uCtaIEiBp+HcGZmZr6zrXgcv8ew0551KhQeqrYtgIACdwKyl6C4GXQB8euvL2/zCIGsEk5oCs/Uw72w0KSNUBt5RH34ofN6vd7EvcSAQynYwOXFCAhNQQLhb9RpXKKgrtYKEx9xbHuNOnOzwuQS4eCdWOnFnMyabCCTgvi/GRXs05Im5V3AR0EBoGDQVz4VEELhhugpMKYABOgwrNuKFrGAH0QLe3bm7dkaehUECRDx2iyAsG3ceFJ5N0ncK5xQAcIzRDwcb/SAeKB8+vHH6DK4HTI4oSnstK8JGx5GbGidtzutdv283mwPC0y+GSgG3xdXFkQxdAV5pWdYNhuD9VKJZHZ6fj45oS4qTtPhlyvQdVu5xzkZuoe8RkAYFNgl0ZllxO5L6dyaUUHY3FBmUe0c4Dtn0rlceqnnT87HBwWA2laI6msZQiHBgUCDg4XCh4XbircWb6N+A4YHjDBn3p6xIwgRcSxzOugzqhh02q/pQPQ1hhlLQQQs+PG7pz/0Uoi+xdgOXVN2Ti/C7U4T6YSw1u6g4OH9XUNVZl0DzS2VM3AbUb+4XIEhQeeVvyx3m/w3QGhDXGXLHgmzD3DCOx22DAy8ljYpaADIZ8UK6wtEhI8rItbK2dlt+CKfdeutxWIFKxnc8J7lzIKIaKWCWe2KM2vzPuM1RwBBCvfe4S4G82YRNEXZCAogxHIHH24Mcic0hdK5C4jWcG8jJIk43ykJHoI4LqivgpqeFDUwIQOCtKwGhK4AWyQeVEKWF22RWcLQbQ5pJX2+jQqKBe94dUq1Zs6sQAR4uUJHoXFpUvDiwPSgoS1ukQKAKMSj1ofW2eM4WiqBEHHr/v5+MSFihMX7Dfu1mbedShREVHgSUUUuMasD0dcYQTBeFkfM0HZwvBwsXVaMlfqwC4h/tsL1kAAiGHy/ASCCRVNFjCogeD4Rxsz1RSBG/KoyTTyoxsghzs9pgze/xtDjM1NtiiGDGSl9pZKlM1cGQk1nel3QplwVEEIBowzKH+iIiMd/PLP++utscbFYjNBsVNyiwyGiBaSTDhKJChHh0LPjKCDu1SKEN0SUZH/BcRCL5CUmnNAVtjsSguFrnVBIvmiLX3fqwQbFh6IqhqYAWxVpo8wnYMq3LAChd40KjIgIeD5Geaal7AYF/fJr8pZfyyHCpNNTU5Npg4IESTuWgYY6GSSmCFa5vNkH1fzeptFLoYCAwl0IBtFEAgdD0OExZ2dnfz37+NlvNFUVKdDKZ4KAqFgYbtgVOjkAAQEeZk85EPxM3aYIMRA4BAMSCH5Xq50XFYovYdj5EAWJcIsPLNoyRIhogdEGcCjxpuqvIMOCDArT3plr9QuzApnPcctmBf2TqJu/gqLqPykwNxX+pbhFKNz1PGauq9fp6BjYL788e7b+ysL1KOIDbiAlEcVWFJPV1G1Uo5VZG8MMNxBPKCdYv1vABYScuC6rHEIpFDdb551Oq9lsdtrNdui8PdxpySyi0xuN1oOlfb0ilA+qW0jJzbBofyZhIUsaFRj9eOt0jOlXrjIoaBToLWwuhRbvNTmjgt78emKpH0LXUwAQWO0EEITEsz8+fgPJ4zqms4EBOgzajoMNqyaISNiOAOJdAoIj9aT7evK6vTQkgYCBBo7EkSyGUnimlyh00FX0SGg3O8Och2ZYTFLVO63t4oWK0H0YC/+TR8Z4DrGl6mCLExIzKqhF+7Ge4PTqalY7ONWgoFpCKDAa3kx7ZobEzd+HMbmZXV2NaUfWX6qg/pBgFhbbQil0xBQQQuH5KC1942gp3HDc1PVoorq+vn4jCkO3AUOIiGPLskFEolqbrSQwOeF4gQCWqhLEj3zaESlUeSf43U6ZaCDb0YrxYr3tShfo4bzVaT7XaZ+3W82QzDPPAYfnuG3dB9hEWF7wfSRMNqGqmL9+1aig6mo0xCswy5OSCe/aGDMoMCWxNc+F+Ghna3TAYwYFlUSkgAFMzKhA7KoKykYJAzniTiqgtYmpnoL4Bs5j9K2sBTCAfqK6Hk2sr4OEONLNp+KJaJyIsKjXsCt0tFRNAQGFu9zF0A0QcAS67/303invM3A75E64FXZ7A4wWb/wQJiFaf2y/3T3tbtbPxTvESBOs3K9VhPJBWAyNPh/LZueTQAMR4Z3pbExc0i/FX0+bFVR7JBFoRlGRq9N8ydTT1GYFlcRO44F4WoXCiOdNswKTdKP3G0sSDZwp70FTBgX1Z0lEREZ7J0UpPGHOCwQpyC9l4Sj7SAEA0OpmATQAAgw7YRELL/kTzyxtB1AIIO4iibvICS91HqcPBRBfdw/HD7qlIWESCKVw1OytbqL1ORGd1hddbs9R/6FWutp77mLoPsDYq2FpIbmBuDAxPZrlG2NmBRVRU6i/VR4ceCo66knKDAoubFZDqSR2jYnsZUQlKEYFpmRSoGCVUBA+uOOUsRTKRuH5Fkdh1VQKAuJOKHykLErrWtxwzNw6zj0mrLABMiw67horG1jyrP0DBGLMk32cYO6tryQQ5fJO98teDrHLT2PjVtivh1SE4ERsf3G6vXfa7X58riYvXUBoCq7mTIaVhdxUvEMP8wMmBamhJraS8gse0+oto4L7cTq0KhpB9BrqHxgV3LFoixQmgJHbB2b2QTW4KAURBZS0fsetcAspeIGoJhAJPhRWiCZ6m2LRi47Er9i0sGHbVXtmti8QrE+EeFEA8fK3mJrqHvTyy329GCtixSLUeuhaGIlD87Vu9zQYPOh2X6MIIZNKdBnfmCtCpA4GQ4AwKHgz8Bg1oniGpTxVbVBQIgKlEWxebAxmVlAq2ZBozZhn0tXsg1dipBcYUlwh5plQ9gDxBClcd31ZD9mDBAIHRiBaKCScWceu8aEG+owaASGu0qQ5odnTlDgEcZhleXy7l0McCye8CoM7fMm73ax30M+3W91ucDP4cve5Zus8TKgM453wtbBKKjUFZYgESSQOIyO886C1reQ7sh9BiuWroCK+CLFboibVG2YFPLkHulk5AZJUHxp/BbElUEpJH7LqPX8FVQoB0oQMMf0jBFJKCGDYCRAoOiSqlE1GwYHEQKBAmx9u/M3Z+bw2U4RxHE+eKmGM+IOKPfQUkB6WJQuvy6KIkIvGg5hLILmE99J4SA8i9WACwVuIb2wMDdQclLSCkEOy1ErRRmhKSg+1p/cSQg+J+E/4fWYynWwnmd3X5+2bTdv0m2d3PvvM88zMbjCvRUgUaYXEaJTPSyAgEciu9TnPWHlri8YghPEEUzrxSKEz52sf7nmX8QwRoocA8fVse4JBqgke5pN5YvKKfiB0HwqoJLhxCrxF9ehQ+YFGMSoEh41L/ACWZDamRwhd4ZGEw/uJrN4WzOiDeicgVeJEqaxU/TJcgdHfchQ9UvDk3+oRYqGAK7Tw9YQqSxQZuG5PJhILFGRSMdqtAoki5j6Lq4FYYzKv/Pzs/G6LX8zzlK3ZjVcosUyIcmP222SIjPKf/PR+536GTmR2n6B1Eu+am1OFiNRiC8vGVD3aYKFAqANJDZkSPGRS6kBGUVAvpaovu7LGYOsV1BsRUu6i43C1wZBoCi5CjOj+snoxqAOBUpNWyfFCAsnjB0ffSCCU0UwXIZHPg4jTJSCg8GgcQp/z7Pzxx9+37e9iZ8mDH/8+aSknHil0CIhtAURidvVP899/p/eWSjQBy4Qv5F6rIA31pbNAAPZQ/1dkAsEMCsER3xwNIlDhmU4HL/9i6xWCZV2JibHTXK6UywaCh0EhAE5J1J3fFqCgqgyzD4H6GQqsFFBYFyGg8CddvynmuvkyKcJDAMH/LYyKUSyy3S0CCMyG5/O7GhAGYyzWSZ7jct8LQ3PGz6+mfAZrum3zcanp5Fl+Ss8mKqu8PzECoY6i5ykg2EOuWZEtErkxMEqprg9VfxxVAV+p7A9qkW+gDAndC5mYFnQfWLTjIPfCUwqZ4F7oQGCimwcGAqKHp4gQgoefZBGK5yCCZjR6u1UKEUUJxGvambEqkSD7LnmB/52AEwEFdjDjVeUkMZ/Oab2UbdkzFB72bDqZJha16MQ61BVMUKaprHj4BjzADApasKWmUMb0c0tXeDTpjWEIZSoxZZH2gokhZ80H6YhRQXnhBBRS6yKEUHjyPQxAYNwJxSXFCXQW1Ef8ekrPiQm+QSBRRAggXg04YbbjdpvtaUAsKcQxJEXZY4LGnxAoFt2EPb23E1dYIiPyzfmZrqA3p2qT0hIQbmOpbjQoLEeIDM7qTM5zedMWsFFmUmBKoUCBIZ3DUGnKdQreUiQP3ws1GPJDqeC4pJBzA3WtUeFhkyaFtFTgUVLfi5cWCkc0ykDXdmPJ/ZNejxZTwgDGT6f8Yj58Q0jQZeCwI+o1gMRqIPTooFqn3T5utS/WH0o2obAwt/kkxmw2XV4KMd2eTWbb25Pp/WHc1Bj66FjOsqyGBEKeGkaFYOKIw6fPWEZASlkq/YgjJW9WUIMhJQePyhTwZgWVyTglT+Yemk4AiFdhf1I4oA9BQFH5HphAQvELWv/7X/I9DFDeEBV8NTZd0Ue1SLXJibjkQMCH1Y2h20Vyb1MDYlnh5B4L8G0ggLVz29tTW3YSYnnlbDIBE8gpDQp67xsrAIiM5hkzKqyfwHY9ldSF+qBryDCj9M0KK405Baa8NCms02HZQgqPeoQQCkeiy+hhBLKH20fRdVwg5IPvb/I0n3WDnEKkGDA8PHlvV2SWbxAQBOXKKkOPFbHz5NO4BsSyQvzHwx/nVEkkRGQAEdZcZA9TS1Sj832tMTQfgmeHByAGi+/1hE5X0IFOZT0a18pkfqgkKKmTuxnmQ0DCKeSgISQqSj1UgQWc4AoP453MqKBeoSsE90IHgq+l7fUUEDc//QIeTrEI/6b3AQHxCUoPsiewP6u7uwDi7SAQhhvAwURWec7ad4EDoSns36PR5SVb0/l0OudYzGai1EgcSNbWKOgRwgEQaS0+mHxQL3J4CwZMDmqYFVSBQRQ0HmuI0cZoe5HNlVY44UovDQpyL9yVCs6KHGKhgAv3YIKIPwmIjxAXfjolIHqnpx9QfECBgZTzFCUpjIrP6mog9MiwtGuUQNyFA2Gj1xBmWxZNbcDuMZBNyeZ0L6QxdA6zAMJfcbvDEAW2fkaE131mBWU6T5hpEyGCRdgLgw9he6GG1lYqZCSTOhBHKCjIRhKI93o0Y1HM9zBzgfAAQ0fxEa7gg+ElAoi8BoRxoRfjWWXsIgSI8pQu3Quabc+vkE1SzDhkkc9OCWQKQNSXksHwCKGYYXI2TMyMIEd3XihCwDJSAkOkfHLFxbNcJAW5+kspZMiJrEc+hO+F4rqiFH4gBYf2wlNvogHxPd1PCkZAPKEIMbrM5wmIXv6U4wB776+PP/x4Y9yjUmSE0rOoAwEv9LsjBu9+vmmMEFBgB/MZTyHsJSISconE1rHUX6+gmc2BYAE2zQrqVQCh5KT4MRUhNi3jvVlBaeQAAkiiv0zLedgGC98L9QqCESXrsg+SKLOCWmEDhS4pNLjz5BScWd9ljE7JbvjjaNQ7Oho1qxidzucRKJBFfN8DD3Qnuo3j588/LG8gkFTXAWFeYvw0uXd3YQYiFt8/O7vC0jnEBEsCMZ8nLDtxL1dTGhX0W83UbLu76gImZlQQVqIzSZDBNVJoTakQ6oNs//RCKiObU4WYKFiXZH/fEL6wiuhyWIiCYqpOFAsycmoAXzmpA4HreW/whQcwgcBAQKCQoJ8jb0DuwJPJ0eX4+KvnX7XGl8gqVwIBeX1VhPqulTxvhwFB1jrbb8VZ52HF9Vbn5PDw8Ly1fHmBWWHZj75te8HQEP1QIjp3F4ey4LquQ4c2EpTq/eSCzhzfptwKtqExRu1m0Ade+CoflIIRa0eGlDTfuuJ7KaEDIT6jdW88Hl9SfzDa2GgWnxWLeXyNaMYbpQZuIkRlabF52Xr+/KvjJoAIjRB6rIi3727DgYCxcudw6+pqe9FhlIMyZgVtAGBg206wvghFSk0SojlRJagyQZ6ckRVwXjdK6UClofAMRUr6gFKFFITG8ukdqdNhpCB8sFSVohQ0IMrlVosu32u1yiDji/evm/lnCBBIIj7dK18WT0dIHAiVL0bD6vj4M/blriGH0IOzaqC7diQgWoe49+nWLSfCnl2dLZ3yLxohBBAyRkePEEzLxyqNTCZTkX1vtBgTXMxnVRqEVkV6FhXrSlDBiugDiykoH/uQCRwiPUKM9/bGZJdjbC+rwyrmMzkQvdeLG+Xf8yguiuPRJV5RPv7qq+fHHIg3DBFiTR5xEQmIMi7wvr1N3l7dIpm4UokDe7EIIV/v27YbHMeNihTj53fOQ1pZSPiCD0e9gdkHlQJUeIGSFST4sjmjYw0f0nUv6z744IX6oJU68AEFjsdJELmlKrk0ID7+eOPDz1rHn314vPHhx8NmsTksAgjwAMMTcPD675djxJAvcRfUL7EpU1K5ehxCGaaD3GzgrKaxSg0IXWFzK0k8tHHV3u1eazOAg2psk0IwHtRte3moSj03KKhDaeUW450D2lhqkNGsoCTqcibFsni8srwXUtB9qFgRfVCWtny+dYQvDcvVFJaBuMwv7BnnoIoeY2G4PovPdY/xuWvoU/YofpT3ymMOBCKE6VB6/c10yXOCd5yKAkSnDSDwdYBAcRCXrag24RV8MIvo7uwoPNSTaEhlLB9lu9f1rUYdxh+7XlZqhSsQECnXhYZlebCG1e16OFnNCspXAYTrZh0nZzVIwWqQQjYVXYGAcMmHwkKhsqygA9FE86Lhq0UY6ofhsLjgAz8lKAgJ6i3GCBOtyzFehy4jvxoI2FslnlQ3kA/WBjnV64sZ8JPgxSG6QnwrSdZ+ekhh4nxNWzKDgjKRpe/U9BgDMyq4Bb/fqFnrLGNSkGPmdX+wXqPhhvqQ8nJIBNcqpMP3gjl1k8KALSsoIK6HsGZzOLy+HjZ3m8MqWBC9BRkgKdL3v4+b1SqHhoAo6kBIL/wCb1fHyRSY9tGdyY5pN0hh75bzcMhO2le4EcSmnlCGKbDgkghnp09bjSqDAmJriOXMPkC+FqLQNyhE86EeppC1QxT89UA0CQjggACxi5gA4xQ8IyCIAvkUEaRZLSKkrIwQaTpO/Z/FMe87uQLzAitTWzjnWQjXnTbxgFvg7t+iz2ifaTdbMEcI3VwAoYqfiBGib3Pz0T843DwvZ8Nq/ZrNzQlT8G1uDb9epwDtdbv1eo1L2MLqIQps8cKBTwJCgUSVQjbiXvThw2oFZyUQLyMyEBDX2A6Jh2q+CBMhoiiMWKD/TXpBVQdCUBnvg4jGYmFKjfnpfj+Q2T8FEK0Qrn8UPKB/2Wqj9zhcVXJGjxD02p2BCg6RIgQsW6vV6/KALX4iG8H1sCXIjAqpvj3oAimlwAZcoUsT0STmhig4Ndv3Az64giik6049ig/weVCX43LSKzIHPnRJanWEAAvNXTy8/PL1cLcKExiI/kIZp4LWQqwFIsY26bM2KRMs2A3W3ykFBwfP2+1kJxQI8MDnLA6TCBFb8ZX37TAoaNb3VxEVrpBabowub4qaXaPTskZkhCq4eOslINw+lxDt41OICFNIpWLusg+O4AFERPUBCowQDFK92Avyx1kHxPUiQgCM3aqkQeCAnwirykABKNbmEPQRvHGKD+/Uajv+ozbc7FCfYW6MTpJuhyzugotqg89naUv7TQraK/26xlSYgoy43WUeavVUzBkMFocyFY4UtfsgcHYPHCjUUrwWhrhRQfPBIx98l3X7L+TDwK4HiPKzaKQ+/UIgpQOB/uIa0QGbJm98tPlDOolvm4qIapEHClGVrIwQ/Vppk97Zj2/WvHRO+8iRi2R7PwSIg30mb3maxAjV0xUBwqSgxxLX+z8RAlYnomXpOiAVV7QtCijfoKDexEGFI2P1DhoTW3zRBAtKsGhISR+gtdMHklxB+DCIuBeDh/iwrJCqQTC2BoghlRhod9XywhAe8FNpouMQKYQCAhrKic34pucJ/wcxH0CwRmAS4TyZvFh2QldQ11fyguO2s+peZgYFiaBxWgW/NSjAWCAdxRCbPKQ+nvloDNe4F9LQ8KmFgpNdgOE7vHF36gYF5XH2wYesI6Scft0VPmQjKaQApVRwxbZPaNahoCeVpDCk3kIUnTxIwIgCChBVbKnaVDzwUQg+NvGmBgSyyoa9UxOJg5eODeogxI4vTymV7y5aa3dDO8FP2nSHUt2iKgQAYuqfWUHWI3TMYo/Mw1k1oABhVFAQOjjmQXPpNP2PvbP5baMIwzjlo+ZLxYwdp3HcxsQkJDI+gFBlBUgKbSggCvQQRBURUYUmiypBWvaQNBsJrSpASiWEIxlcX2whW7WFzMGiSI7xIRwicKRekCUOvuRGlD+Cd2Y8eXc99qybAAqSn93Ya9f7+J2d377zsdvka0gQpDOHnzEGS7qiPNxxjEGU4g25FHeAB2CtHRC/AhDQpaRbjAeRFzgLdh6+sAGBt36zILJZr9f7QRROh7kP3e9+ln336zmsGPl32UsO9s96k3wmS76zX+Ugtw24ha8VDig6aibNtQFi3TqHGLiiUdwWqZ/pdqcOt6UYvuEOL3fs8DK+wFJgikEguAMbboLoM+QHpl8FFowIe4LgXYgWQHzw4Z2foCNOZyjn3N9MuCe+/vrngBvlDIS1NggjAoDYt4N8Fzj+qByQSrTgi6iNqINDM4ZIsiDizkEcfkaiOnPAbSyFaLRkIFyswQAlEn4GAp+owr4kCB4wQfCZqj7rf9QhBPLUxJ2f6V2DWS87u4lcFLzoi8WwOthTHXOJp6UuREcO+GEU4uDsIAtzOB20de4gh3Ebm5yOHORy3EEmO3FAnLEUSBQC0XAAFFxM/pyfosAlcOBpgq0cCNHl/FYCgsvtJPFBQvzODoH1AznIOrhD9I0DO0QP6ACtwEEdJiQHOxC82eAXNSgLSARvRTgOIPGMQPgwCKvsR1+WG4JwdnjygA6gf9rBfQgc/pVSHGk4uBgR/i2odkuvkrFhIUKMRPfAaAbC9nW8KmVZ+wgsCNnB/c86oNF/7eA+iAMz+E9jQCD8ftcWiD5QGkSegJecCVjhh1MAPIguBQLhgyC4pGYLF6tIQ/4OHIT26cCfuw6dOBxpOAAPbJDhoht8ImJLJAmeI9ijIAHEssavCIQvS/alga7DYXJgQIAoB/x+CDG84A0H3cRZCP4DRIhmxLP3O6bGhj1Z7z7kf8LZ4WVYD+LA9/6fOrz838ZwRDj4/X7oVbpQW/gC2o2tlnI90QCiZ2xoeHh4bGxsWCwWDVH1tVbPg/d3HZQOY/9tDEeEA6xjINwT1c4jQh34r7GhH48IhZlGG+pvaLChXqsiXYdD5nCUOQwPwZ6wNxfuy9S8a6ixhsLUgVEJIYADhsAswqPhPQvJhKvrcOgcAIhh7oAWo3zpH+X7g1QOEASPoSFk0R4BGqDDka7DYXM4OtxwiMgOXOhgswlxB5AP2pNIeFSoxb6SQuwn0nU4fA5H0YEprLAIWTYFDyAfJJYWFEktTbNGuw6H0OEoOFAelA6yBfDw6H0NPdgXkXZX7Y0xdB0OocNQeFSZVEItNQo8CD16pGe4WUPqQQp9q+twKB0eePDoQ/vQg4/e11VXXXV1D3r8yMC9y/NY1+FQOjz8YOKRe1bC5jBAnrx3ubOPdR0OpcPkFbUmW6mODg94Ak/uJwji6jocPoeHPYvvXUHVJ/cwECBIKOzs0KdHMAafd39B+LsOh87hYZ9vEXF4r2bEarVafRJpkLSbjlcQCErU8Z42QbjpypcWIsTfdTiMDhyIeq125UrNKGpGsajFKBNyZtioAA2/pWuTCAQwefw4o1L+/uY35CC6DofMAXgAICYBhppWNGq1YqFQKGrFohHTaxIN8Y3d+kalvrG7W6nUJ+sJEQMNQg6BAWkRf8MaHPH6lQ6gAzu4uw6dOvD2Ahx6luu7O+lMATAwCiCtqmmaYej1Sbo0VKts7G7wjWBwY6cmMsTj1EEOQnFrLGxjEB06XJ+fuXnt2gzZrwPG0HVwcDgBQDwSS9+6lSlq2uYm8HBX1zXIEcXYZJNqwY2NykYq9Vs8lQYiGBAPgIM9CHcnwiCcHK4vUxae4rpGOnRQqOvQ3uFxD3PoMYGHlaIBEJQ3i4WyWS1rZehK1Oz9ykpqp1JJ1ndSqZ3aTnqXAwEO9iDcnUkEoXCYvgZ6yi7SmYNKXYf2Dke4Q0/11q1b6SJLC5vlgmbShiNkGPYUsQP5IbWRSm/Ud3drNdGp7DmBQbQIgeBD6yAUDvNPSbrp5OAcQ9dB4XCUO/Rot26V1qumuVmgKULTtUwmUzpZNeq2wWY8BWOMVG2yvhH/bccBCAKrLQIpGOdiEBmIVTdKclDHgA+k69DaAYEw8rdK/f6ErgEOm5uaMRoqlUqv+0w7EMlKGnIEDDd2UxuT9Xq9HRD4VbhxfXVhZhWjURUD95xpdByu3bx5cXp6ZmZhHgsoOahikB86dECPAzqQgzrAxr/vgEBA/fcOmIaerRYKm4PhSG/pz9K3W/5HrJPWO8H4Rjy+Q4EIplIbO/W6DQjmigEQN7Iwc/EaT/iL8q/7aOkgMsrC/PIiGqGvs4P1NJAzVUcxWHdaz+fQaz8O63FDHcPBS3FwBwRiFIC4eyyXyAb0Qubbq/3VfpohJiaqtUkkop76LbUDqSGdqiR36s1NRkAEISp0GU7pmemLN60Zf0H+pYKyg1z3KLQnjg5YffiM76sc5KSQDAbXpXDuxWEdDKT991cKfCT/sAMC0Z/Pp2GYaWYTmlGeAyAia6W1X6NbZRho7Gm3Uk9u7MAU1u4uPIHqNiBIU3Vef0rWMsaFQUgOhC/NAPBFSOWAhT41hcW/cPpV8QpEFA4SlIEgKGlic8wfO3cAIBgRhDngk8IBgcKvk9Lvfh0Ivt8yQxRhFkIDJXKGFtk+EaqGQ6VLvgEYfdYRCLjmtfPbHzDarPE+ZX2yFRAYxc3WYwSirk5bnUsIIBvtHfBLxs+PjLy09+ryyMjls3gUnA8lnuB6kCkfwCA6duDSgqCKW5IjUlKVEqOSQDIdjyTuKbEstmQg3iz++OMKvYQRS8Ri1e0Ta9XNwVLvWHhz0wAKULt/wIwmvFPfqNHOxZXl9hmCsFHjC7A0dA20jCVUH0oscJNI54fy1GtnRqhec3ON0xeXTwkPNVIorE5Q3CuxqnTAzxls/3UEha8dnd+BZD6Hbjnu03GuxfKi0JttyEDcZUBoRT1rxvSJX0pwa2Yo7AlHxrZYX+EKx6IOMOANEu2AwBAuwtgARgary4t4HEUYimLYg19doB0ROsxYRlI6qM63ISFwNbLCedjEHKGEUv6LxEGulPQHN5wduAxukLvnDEEYjvHEnlWukWuIcylQ1tonUpPTEoh0Yd2IZaNRvwlADPUMjA24fCc9EznBgzVV8HlsveaSgHDuD2KzpSgGfvL683uaRp8OHC6MCJ1nr8/CliACa7PDDJEPcpnSYFTtgOkuxg0Ma6dI7YDfVBG5iTAgMNc4lgKhljMEwVQhHBCI/lv5dMVIZLNRICL6ZSnS43L5/S6fKxoFHmw4AB48acSKm8MSEAhuq549biqKwSLEBs8CxL0dyk/PCSLedoNepDCcs+UIpQOuoFSQKSmNc9BBnSFEL2Q9lws0ce18YqSDIF28zDWcnEthl1Qor5YSIycJiMFSPn3X5wcgqL78fbDH5Qd5XNHt3CRIoHDFolixMMSBOC536ASUfLG/K3EtOVhhmmUwvHBtaWmhCTClA+js6RGud+gOlIULZ2k7cg4AIY4xoMgeEIZbktoBi2IG98RnJBxjwPMqbemQciCQEAcHXPkzKsddJSC4Q38pHxplQGQpEGshBgT8bG/7gYM6kIBQ8ARR0wqFvj0g+ngQ7tZCEOQMITk0Ta5efH5pen5RnoNQOQi9OsI1xVuQy273lCDCwYHgyqchmPL5FJUp/k3hYN8/UYkjENiTUDngBzgQafEyx6kKxvkBUjhIwojMFIYiA9FHgUiHPRQIPwBx+0bI5/IzIqITJ/mAgok+CTLo9a+IBITUiZZEZhc6qU6CXKBwu6PKcJ/FXsQZmiBEO3J5Su1glzdhBFHWRNGhQywZTDYqQM8l9Jgu+gNKByx/msFozxC6kUqIA6E8tazwcXnNSjIolGoNxGBpBYBwZbPZCQBi++OQb4slCH90+8ZalSYIkScoG3StFTKZQlUA0df6UJLri8ur8/PzC6B50Orq8vIy7x1iELKDNaEQ6ZmvagccaXJ9SrfOkanTSIhjDHwNrKeCzdLFB1QOGGhu3fS6E3zXhHS6tnXAUqcbNUcsfQgTbZQOlWAybZi5gNfrDSRM3ajkKQyoeAsg+hgQK4M+V9bv4UCsDW25GBHRuVAotAUs0FGFyYmgUNSKGRkI5JqsLkwvzT7/XBstOBVDavQwZ+C/KxxOvW0fabx6nvUlGt3Mcy+ecjyUOFyUlXCuDEkJzNDIQicOCASXFQji7BAPKpXPtQYCLl3k7/YMZP1DbwIQEx+V+nmbkYj+EuoN/ZWr1eo1U9fZ3dh1+qBnQOWjLTPE4sXZ59R6flFRDGKvd1nzS6vsWQXEufO0zt8ZaRAw9crIns68xnEgSgcRRz4oy+uYIaRkJoDQEzmck3F00EwVEB2VIpFqC0MyHQsglBIQK6HqgN8T6aNAXC2F+gYgQyT8EydC/YNz22bNBEHrZ/KtegzyQ3nY1xKI6885asnpzCDWRmd1nmuVNTmLM889N68Cgs9CnYFqF3NT0Hl4STQW426UuskQ3cl4owOQTOQCgUAuRxME6TxD5LR0Km7L0vkYsqLKc0BCKue29CGIHYjOms6c3OjFU2kj4UUqZSAipbVQqOpyjYVPDEQnAIi1QY/fz1uMSNm3DROYMV3X4YEK0ChmTvzimRhgQIADC8Itgph9VtZzs7OzS6CLXIs4OyY5MHnhmvlS+0wD+ysdPh0ZOX3qU0uP4TWBBp68Cgekkp/RcV4lTSJKBwJruzM0rqOLIgYDPpmzANHUhyCODmSvW7yepiMkmG3SYohCq4tb3KHaGwr1hl2e4XDvSQDiRGmtF6amXBMTJ9fKVcO37Td1UKxBhEnvqPro6hwH4ji1OGk9lNNIwdLFaZi7XrxOFFO2ssMyNDrPKrVEVA582uEyJAg+psAEcW4cDxNRORA8WgKIdfkOpHYOyi4IzjQ6OeSClAgEgilgbTKIwkEpPCtkIMDhmNEfCt09dmxodHAUMPh4ba2/WjWqnp7eUtmouqJ+nQEBYs9aZqX36kefT7haA7EINTa7NDO/KGHQIRALDZ7Y0lozSgckgM464IQE6CU8kEoHUecIhNH8rhOUtPLaKubkIOY/4gFsMiQglKXAcbs34EbpOmm6gi4DUTWMcOhueCgCy4Brbm1tTTM0AzQYKmubx1x+D88PwAOsRoYC8fGNL1mGYA4nPVYqF+a9rXHEFYOQHYAox0ZnWu0A3cpnuEbGoXMJz5fHx0fYG+enREBKB6SCV0wyn5DK4ugQa3TfUqmk1GrA/o4OGkMBJ6aovAhEopJfVx8HInq0eayRJFwqU2QIcAAgylU9fPf78FBV14/98snKyspdjYr9p75q1ePyVWOYI2IMiB9+uOGTgZA5kK9jOx+IpT0MaKMzAxdMHRodOYYLz4zQZeQVuIwBGNBRxysACUABo04HB+l6UA7eRxGvlzjHIC56x2OsLjgFurGej+NshlMpvJwjBIIgEI3uiaZ0QIZSRBAQt17GJ00Zgjv4IoVitRoZrZoARM8XK6BLZU0rrmtGzDCqPo/LA2zoTABEkQ45P/rhhx/6GkCcxCCw90wXa4ONi3x2Wh1EmzF7UdXoqB1AUywf0D7DBcDgpcYlcZ41zr0z7uxgDz9nxrQKdMyS8XhjqGA6OnAgEpj9+dUxb2WvhomTQy6OQIiIOBAEXKhSagfC1jS/bIGRGA03KUNwB99mJlPUjMgxs1rVyxkKxNonLEEAENqmb2CrqtE77HQmI0MVAiCOUyAYUq9jENL8InG6l1B2IDOrKhTUDlynac2zK5vnn3nmU0EJEDHCmTj/0itvn506O/52awdULraOs3vS/JQiBt5kxBubKQYEbrOadHYI5OPxOAdCiHdKRUgplYM4iCabPhGRYL6R76nkDr7NlUwGkoQJVV4trFBRQjRYtWKhUB4brtIXhs6QgARBP7CyhkC8LgUhYUCaGl+CxZAdWorYFlB7B9ptAJ0+xSg4zQebfPsZu946q4jBa/IELwtrVFkKHes9b4EghnAoHJjki1tujCgJm2mFA56TdB/dkiHy7YadDYcTtIYLmm6a1WIG0sOlzFqmCCwU+UOxOgwtSKFgmKBY+RLjAX4YEAypN/Ev0u9V+hviK+e++mpCmnfGIFo6cL3R2I5+9/SHEmEKB84DtBOyXmQ9zT0eXmkfQywVVCvnVpYClKMMyPPPXgGE8jgQtiIQTAhEUqfTHElT4cBFGjim6ExEMgk8iD4mDq4RCO5QZkmhCERocPrf+PiXj28AH0XIDgAEyKhuavBSY5NSn1zKgApwdStsB8JtT7YfAAagia+eBn2GqEhnhuwgPvkd2+3l96nDdy+jtYMDOU/rutFPGJ+yf/WFtyz5Ybx9DFYcUun1mJmiG7mAaYhr2THHUnjxXKxYJxOSCIfaoRkIwnZmr0VlqhwIj0JPy3kumTdyLfoQfQIIqkIxphtQz5/fvn31xqWVwjolggJRpENQTaOPuhkrwKz1ZuTNk8ev9nEgwMGHQWC78P7TH7jfeJfSwOsTexZWKmUHhGbu6Q/hAfZ+H6j6KmpvNto6TJ2Buj5zljm88jdl5/eayBXFcfofyK39iYXSLCzdQhBkg9AaN20otaSpeSjkRdAllZmlSZGEfRpZMe4a9mGbaLBrhKJQWSPiPkg3VF0fE0gf9rkvffAt9J/o997r9cz44449TeOkOt+cmfuZc885dyb96vYK71za4ss63h7Z2nwfyqrPzDlQV2bFS++J5pLbUVTGDPSkXMkSsFFjwU2BERCMSKUumV5BH+iqlneyDyEVRkAk+qgqO/Vi/N/hTTGNpEIA0eNIAAZ84W0AUU/XP8onk/HM5WsA8S6XeFc6MWExUHAkULgFO4rMuUV2WoGgOLoVA1EINVEuASLI5iqsPsBQfy4+8PArkMHLi9sb9778+ue1tZ+3vgBLX6r5Yo4CDeDA6/GWJBCMdw3pmoXV3I9iIAOBrzCo2Nebm6rP5abApqcMizLaBRSyLsudPTYVId6BQl/wYP6EqvOzYisfGu5iBlnqwAyjByLq4KFjCB6yBtKHvXw8nmm1BBCIMd+SE44Q8fwWeNjGCNziFp8xnOxDh8LUg5RxrnCEyULOGosofI2hfrAqJgcRCr7kWzRL3BNxY4PzsDlHgZLA2ggNJjuEBecdkk2Xo4BlJQDegexMVQ/OmRJXcGkVaLapEagCCDrPGgU47WZVRj4ACKGggIiefJP9a6nYyoRv+sgS9oADjE8c9U7B6FgFPmNYvMZI5ZP7l61WaiYQZEk+iAgMYQEEj/iLHQadjojaUSUiNGPMUdjiDKzwQX8gCdiUOaayr2W18d3tB5taHyrqVmceInxinqDU3OsCBCXPJQw8VX49qvwqinz9eWBOIFjNXvNqkSJ89HYwAcQ3Coh6ffviJGt1r1s7u31kEokrC4ZcUmYRhiVnDFF0JvL7GQUE/i9NE1zTsGMYYyJSwJKeedc3KUwvu0TljnGuEJ2qMqYV0HX4blN+YPPr78HEj3xTZQ3+27d/UM/xML0PJRo/4+MyMsnqx02CWV7vWgW1r2G7jcFStNGmVoFNRAhvlWOqKhzpjVah6g7EwAEEUkoIvN0/40TkdlIWB8K8AgL1hCGAAA688uwJHDBj8LL0rJjKXNqAgMR0biyHc1+MpswI50WIaQU6G0ExT8RmTDlzFH5Y9ZDdk8nECk8yYRuYTPQK5IPtMizYLiZHV0GnoEq6kio1bQKMmseuCswGhK/CKe0pIJirAsgjKzUHvPMsQl8NLVdqgdqBkAp4tnPUjeJA5MDD7s4OEAAQMEsGB8HDQVu0IMzMZWY2EDTmYjhDmDhUzcgWGwzmyD2PkryAVUTRnOGG1NYqQNjw317n236/AOGF3/8VcekOhJdWmKjOpKyyqVNQTBcUVrRGyaOF4Vn0wiAgynwMDfhDGYibghz1anNQgAKxLDZ9B1URITRAoLIAENc58NCI7lyJlW6DUwUWLEsA8WkddtY19zN2IN52Hgb5ui/rzqNkfD8eceBCTmgUYPsMRMHiUIg53tErfOHf+OoeQOAFJ15Fk2rD779H3ukU5BKQM+xmfdRtlUBoFIj/ZnUKCOvjCl0c7go9AYTkoTAqM3wElE5BPLTO5JZlX8ogrq0pIKCAKSORrif2nrxncCD22v38Ts7gNaZlcOPfO6KRuZTWATG1OhxJHt1SFl1wOAkaoRB7PlY4QmBYFAhEB2FAYR3fkTqI141N5aMOSsr7YNmst8dHZLpXpD8K1f9uHozzfWPESOncFUpqI0ogBgcqRhUoeLmeB4KwNIpUTMYXSmzKc4CoIz5kTvZ6AKK1127vAJCOZXWQPwggOmJ1K/uB+R6alOmumSEg3pl/fYcxmGSMRpuc0CowD29DkIVp0sCXXmGNE/HgEWaOZ37/C9GzFIDcoyRDp1CTQNA1apV9NIhNCrY6HyQWB0whJEHwlrJKx0WBUYSojHMQQYaXzqVOoaTQwRaju7CYbaGMOYGQCn05ZezcmG+QQ7TMdnsvjf+C/jSakn2D1xowK3uQyrz+qA0gchlVZbw96QSjL9E9eL6fDIYikVAwHpoVIaYUSEJVrkfReDjGFZLhRRRoIcv/YEWmDhvrHhEhYDRrMK2CQUB4rYp6QseRvVt6H2jI1SiOWp29AnnANApTteMBSbHFzkNp/MlSkxxpjnmV26TwllRADiGI2MvvSCASmBbS/B90qfv9DgIFR8IqfGK+zr9OYYFcA4TdwvtBNvPOKc2JcFokmgzNfoLcHYgN/6gAXR0FhZ8fcltTu2sVsiKHoKSSIj5TyVpWo0B+Ou9rqHppNlkAa2Z3oGDraNCp1CoAXFVGl4kuw/a0SGEKiLcJiHpeRohcsQgY+H+TUQIwiE5lv5hLvc7n33cCAR/mZQBOY8Ekm+J6SkErEZN4MJ2C2vWx/5HabeWH9c3NVXSuH25tHR9/wYSGTkHOtfYHO0tYKDxwNqZ8GgUnvF5oEBH0pt4HRY5BPBAQZDoFTFRqL1tsO1filip/CQip0BeDn0jdmEt/cyA4EdwkEHUAATOMereYyGWS3y51i6kxEO/YnZi2SCyMEmM/uv38UPQap69Od4VgMh4XCndu3Tqkvq5eYc3/WMwTx5sbfqdRL0Kn0FS5V61p9NRw0E2KQESvYPPTW1WPxvB47QgdzPU8KCAs+0NgJQJKq2BJqskY7QxrIjGeBYQsO3PRi3zxWgKRM83Ekz3emQAQbyxLApHGx4og4qNubhYQzqciY/F9PoQTNrrAaTg1ChFOwemkRFKLFN0HsPVIfOzZC+QQTjumAdEoFGyNqDIl5nTNNl0UaNB5CtrDt6qvNCKCoNCeB3UdU5FDQEhjeh/OEccci0Plift1yxNAjBQ6/KY5MxrduRZAAInUzs5Pu3WeQ8AKVqfX61kdzCNd3Chltq5zmTlAkAXvzDJRe+qHk+xwev9bdw7ZYqdSVZ+P+eQBe3T8cG3zd2z8Ruda5wOWMHq2M6f2oD8gUtD5QE5IHgo8elc9Fs0aTH8eSIKp30ZP+6qVEK0CVZvOioU4L8Cj2UBgLaPb2o5GiwqIy52dzE0eQNT7u32rIFsRdZ5WdIutFipTAGHOBkL5HVFjiGHdjsaTwVgYP8T4+5rDcN5QQQqnUAgHQ9iK0/G5I4WSE0CsPOBA4KdjvN5f9ywG5cC2mlUpqS1KIbyLYM0ED6Uyl4OeRKmCcON6FD4fFRm2DIKAgDHX81BTJNlSCEYPrmbnAGF2uzxAmEUxZVy3nm+bZqZxw5vU/f4Ty+hh0dMaAQEaJBC5KSDUeEo7RTxAucgvdNl2jtJokhPTCmRxgICale8ZVVHnVOlrFTZXVSKx/EJ0IJaX/Wueh/KFwrHWB5+tXq9V7XvJ69zFB9WXqvKpQo5rU5UbJTlKOoUD7CaGK0slJy1pV90jhCqWBoTOCOXauRKCzBwgEBa2L6KXHIi/rwHEc9PMDzkQ9faT3b8sDoRhdTgQ1zBAk9qfNWU40uv9OwBBkiEbSocU7nWHQSEiKKYYIbWtkBIhxhUp9vTFCgfh/vLylmhkLy8vP322DHso3dMpUIjIqt/lLYszODo4ahLNU6CnvvFYjKXyOwAhG9DYhJbWByaXW0EQATHyxfEseNarP4qKCCY0SdBvRyZ0rgMiCiBaxdYIiJx5M7xJ8Pvqnjz5q2CJqtMCEGABzKD83DdnA0HlImaIhtjY5omgJ4SfkwoX7dVJdmcEQly8RkKHeFWmVzhefix5uA8wYI+Xhd23NSFcgfCWah6HVQZs3BkqMRcFRUHNOx4LIZdVy87nWh9k4tobF6xZOvAxED5xe5yl9aGAqY1y2GyzWhq3TCs1GovZQJjFMRBm6mbY2ONA9PsGeIAhQnQxYYCHv00TM8ZsIGi4QhhOlAm8Tjgc5QIR12SKWpUytpzuRx2VRljJuyk8XT5+CB4wYwhbeTrmYXEorZLsENOcWy2rAGEtgJQPZV3Z1qusqQ1pA50CcFQd69LEHTElEWu8xpgVnQ+sVHCmuFmZxngRvHwelwiBiFC8HgGRGQ4bIkLUl970DMFEO30mecilUinwoIsQziIhcHh6ehq4E6Wx0AdbZVGnAshabO6EbS0DBxEghAGO0XzBFlWADSzlMLV9rZo4ocxVwWs0LQ4U9ZNqzralpVWg2zCyaj2K7s5pMqNEvS7tUVhNx9+eU7tZHh+c0wIRzaXbfyZGQFw2hsMb4HDVfi+XeIMlcCQR7TOUnXzCuOQ86CPE6PqOnoRjoeSdC8lHeKHhpCQCucMhr09CMZAAu1gMKWkICbA12cUezRiPHfHBPY+Z/JNdVeczGXqFsvOJ5/IoJKgbXfDwsMtRNFUgoZpGeVGBAplHi/UBJbg12qdJJ2IWELjci8WzOkqKogQiOhwOX6fTHw0byQ+WlgwUGhwIQcQ1+tc72MGlMYXcIRCXyUTglQAiwOjsLlY0ngRO5UYgwL+/CtDqlqvCMSfgmZguHvHwIABZd0TPhXywO12mVQ13H2i/Qe/cAQRgOWfuCl4V3O2tSVjN8cCIYQw8rj6Ifwulj6uDQvnc6z3PGj06thlAoGLovnx51r662uVAXEsg8NTv6+Ew/O3SUs/oAYjOmSACFWom1+LNzF81jSkJRCgUCwbjgdMwLHDaaIRRh7p2EZgdiEgoFAyGAwGucBrgCsHQQoPBQMBvTOFw/5g9kiGCLQSl87HDyYXH2oK9EFosb2bLtocp3LGmSHIukoaqs08qvThYpKNDd+QPsmwCdU1SeX0G6wOIHAeiJYBIJYp5APFNDllEbzAYvOGta156/pNIJOpYEb2aDUQkHEciePpLYLZF7U8TzDmVwZOLV3/MVTgFEkynoELEJtYynvLo8GhFEkIhQu8Dmw4Q8qsyfmqK6RScuYqvNAr+Xhohuq1jtoIiwpBJw2Dqr+IOfK5HYXd9tjGHAgHxXveM25+7V7umAKIFIBq5YjqFl7y5NOiAiFqNw/ASkQRY4N5rADQzhwgGXOxEcxjyXIIErf2hVVBn4DfepF599ux4c5RYUtHheHpMU+mwyRNapodbdD44YzVNNQWCYSEFnyh0B6oNQY3Smm8BH8hzJ+MEo/rIFBCChzqPEGb3Gta6RIAoJhJ7DQCRKzZrtXZnMEifvTx7WQERadgsIDxyOO8Ke3VxgggPazROLsR/wRvCYm5c4+PcTi9OICEVTn6xK5y4KchU0uO03zB10KK0XmE6QshTd1DJEiTMPUIw+wNUFUaS/8MHb6XEHLlt85yY1SvQpwiEKZsGoi2AaCOnNM2iAMLMp4pY7H7SGCbzuW6zCiRqAALxoSKIwMdnRwhY8Je7FxjSIP2+kBzNkCcUPMHGH25XRuSPu68ad+9ekAJ7JUBo8OV0LhbSK8ijXqf9VYh4RidWG2Omcogp0ytMWrni+NtxxIvOB6LR69iRKg6tAsU42qCwRWGCzQECNUZ/N2UmQARqjjrPHot76Femct3qy2YTSKR5eKgIIoR15pSdkYgnZAciKHkAEfiBb8dcTyU++R97Z7DiNBSFYSqCSzWO1VHUVQsikm0Qqmtd+QIuAl1MoBaK60AXYQbcDCGgMG7iohKCNAtpC5d2JVjQJ8h2dn0L/3uTzIk5mSQVhAo9hnE6nXw9mfvnvyc3N8ksJ4jeLPWHF6l/uDWEvCNS+75HlUkWUUPgD5ZglyLV2jWtJceRPjP3riDwhiQY4SsIlVtQiLIuw57CIb6frxznKwQhoAfTtM1VEGyGoe9DEHZoK3vIBBFFXBD0WTPszWm8kG256GnBbC7fwKuXtZuh2n3+h8PMjRvG/BSrutIqqgjaZRv+XCM51Pe+xOEPvmruEPTruYGJekkxg9KYLhrXEOVPY6OFC2KpBLGcTjfnK9OUg5BC6mHimSMMUHXxZHA/FGGoHCJVRERdRnlz6ovMH3R95qIdk6Wn6/q80Z/S0E/xNelBdAgqIWiQlK5DFw0dgr5hDVSXA4HYvkrgSwi81ykcWxC9Zv9mNs9AFQR2oFQhKS6ItRTEBtd1Ol89ISZDKMK0cODZ/uz7Z774HKKiPBNxHH2IEFglO+xsl4wzvtFn2TxI42XSwDO3B6FAEKyoLCWg4ZMVUXm8SYWxMMABwa0kcEvUCEtNVJsDf+Alc44KAu91mP3X5UCpciBF/VYUNZhPgTlEOuta3mVqgpLh+3o6MaEHGx0G/rc2cIgw8iOUlSFkgKHr6JMUBBWVlETBIoqtEsAbFjq+XL4ZBYswCoTeKczmFAah1RHUdvJdlPb2RjloVa2CpYKg3i9W+Gz2cE0O7NlElDnxqwncnWg9ihJByFsEDVExfPw4ndiqwxBySi0M4vxx6EMBF4KIPyEiRFZU0tRvJEERvCxuvKur6OVv2c0JtIZhFAlGQghqCBTccIlXRSAbYP1uRmuaA9cSkaoIWs2zjbfZCl4OESdPaIGQCGKCAmI4gEG8PZ7YtpDh9U9OBhs4xH0UlRBEiDIylmc7VR0R4dWSCYJ6z9L8F9SalERTAili3oSQX5hharUEVs6xA7fGObB0iFRDIBlzd9G22IrCKkxrTBDLyXR4fDxwg83HqZp6L2Lh9Aebk5EFj3gAa/CjMyhCxB4EEYvkaCNa5i/U0TTtZsGoeApzXTcoB5nE5QTyWEKqfmfRjEDBa6qGBNoO1vNvS+ChNSbw+mO7HFghS5gCoQVCdm3n2HXdwMVMa5SXwzj++mMwwBCE54yCRBCwCOEkc+xi6EHGNy4IyqM8gt5FBnR5yVYEw9iWwGNPKCeQIKLIfKcE4djQxlCe8rTGo8HIc/onrhXKPsKXgnCUQ8Ac8IOcINqURD6Kn8reRxJ7wi4RWinhjnQECAJzYuTxhpw25ViejH5w7lq+bH5UlrGQDkFV5bQgiD8+Tr2QSyHyXook/h3hxg7k8L8RSBCYPPsaDrFSV14kgnDwDc5/B66lDMKXo1EidYikhiBBtJFEEmzspvykipbGvT1hlwitlHA/juUciNfuMfRgmp6If1r98cgRjjzP2BdnPooIKYgoFYSKaE2CaH/R/ioe7Qm7RGilhFtoZXlpxtiGCZhDUzqE0x9bnjWAbYw9EWWCECKOs9Hr5a2Le0w96976cnDQO5DRS5cmce/6vyQc7EAO/xehlRHaeLTOarVqHy2XTzq3Hx7ePXyFG9y2cav9F4hXnc5TORP/6fpo/fTuk6P1+rBz9OvaIxBUErefdbrd7jNEF//UQtGRcVget69e2RN+s3PHLAjDQBiGW5AM3a1INkMCx3H//+/52d6ZGEmluGTIqzj5PRQMFRd7EmYTohp5aTUNgaB/KfR6u1i8RZrXHtqtTIbQmeA2IUUsbc26tbGvpkGfgSHsd4iIhGXLCMYjIwhI3RC6E3AgkglGEO9CNtoCWvUayuPEPvfIl1AL8xB6E1xSQbJAvqgU8PqmggnTiu8TYbIaW1TfZGQI/QnuU2BqEXmLQrDzgFYR+TpFWrkNeJbRKQH9LYQh/BQcBAZx9rPgeZm0y13oeG3b+hqG0KEQmQ5vKraphWWylvma6uLxj5SIhtCl8GSvjnEQhKEwANcASsQBqBpjTDjHOwBT16bDWziAJ/ICHslOXMB4CEsbLI1hgKnD+5q2r8s//UmTdJutkO4ZIYQscMjen769G22LA40jPetVTBM2zXK8YCRG+QOEUEoJC9Fsy14A8gcCuvAJTV0tVz6pETFKOKJQAxEwz6EP4LsgIdD7hFu1QlnvGIlOws9SohDKsrerQ9AHt0LaJxzXFeLKSGxyfjl1XSfRfxjjBIZ0zAD2mNI+YaYQpVt2+ldTIeLzbe/cdZ0GgjDMrUJIHAKEOyQgkBDiAYJEj6C1iIDCD0CHZBlRbEG3Etsi0brgARBFOkt0p7CEZIGbPAKXV+CfWa8nZomDgGKN9ktix+GwzXyZnXGS9eGTZ89OU2PSZGErBmtBmgDaOcQHoZERPCH6DrASUYgRgGi2QhBJuoEzIm198JVoZIRTXmbg3Sa+EpNTUYjAODY9a4Vg0k2SdtvLEL4QGMEXQlSgJz5RiEA5PD17yWUI3Po6uPSQSlXpZwg3ggix9ztEIcLk5NlLTgjQUwIeeHgZQkY45fkwTBQiSE5fuvTrDEHRf/xAzkrR3ms7k0ZGOOUaySdf58N8fTKJQoSKCPGTEuQDn5V4TDJQ/8FWsBbCL4R4Mt/Nxz0QhQgREYJ86E8XiwcshOtGrQ87M8TX+W6+RiFCRYRolRDcREFbN2FgIyR9Ido2c/477BFRiADxMoTfcDp+mi5s4pARLm4I8ej9dh5FIULGhbNJDcBGlMBGlPCnCz7B/XRDiIkI8X5vO++jECHjwol+AuHfJLG3xE0dfBP4I7CtQrhew+8vohBh48L5lD7LWiSig7u19GzgjvThoBBPtvUXUYiwESHEiL4TooMoAR0sW4X4Ov/2kYACG7tv869RiLDpZYjHLuiJQJWEO0ZiYODDLiEoF4AXMADAjxe0gxhWiNh2hooLp41zCx8gD3DOwMvWAponHO7rVTuEQPRf7RGv5u+jEKPgNKLJQlC0H27ygPIAy8GHEIRPVLUmuL+xI2wT4tSz+fc94vv82SQKMQZsOM+JEIL9SmW6TunJY2PSvhA2l8gILMReT4jJq/m3dlnDb/NXkyjECHDhbOjjCpkMPuEGHqQ5aBZNnqvcNCIE+0BTxoAQL94/m399vmd5/nX+7P2LKETwcDjPnZsqI0YID4yCEOtEK5CvF91MQRspKjGCJwTzDT44I77NQRQidFw4tebPNIEYgcAjMQBVwAeIYais+GTLi91CfH31vbeQ2vdXz6IQwePCWRQ5fLCIEEmu12v4UMAHEiJpz0dBGztnDAjx8acFeGOXMQo6IUqdWiEWHGwkCmwyFiIvCq3UOl9Titj8gRfEGBZCmEQhxsFpiibCWZWFWfDZB3hBzSY2SBC5ziGCLjRPGY1JHsgveNgINwIJsecJIattYmuFiGcqw+b0WQrn5WlVtUIgIaTY2wShrBCqYiGytaG/sUbYcuOpjOAL8RNRiDEgQlQsRJJjZkAeoELBKAihYEhe6LaIMGuTSh2xU4hJd49CjITTHM3LJ6uyyinCjYIQjf3uHGzIqXZQa64qMwhBurARPGvgLiPszBC9E1OTKESYuHAWZbWmatJolJFrjniiSAhN56R0AR9oztDsC7em7sSUCDGYIbCJQowACudlFqJUVEIoESJTBGcJEmINI7BX+Gd3Fhttp4wQa4j/gtP89r53UtOc0Zhca4o4ZYBGKXZBkxJ4gIymDp41eNqgGkJG2JYheBOFGAscznsQAimisOFHwBfUYjgfCtrlFpIErJv2OzJPZYTBDMFWRCFGwGl+e9+HEEgRShPwYN2kmWI0YAuAxg62YIMsIkLYEehacttqiDhljAcXTlUgRVgfQA6w/UmIgncsRL5OfyHE3tYMMYknpkYCwoloTkmIqiysERRwxTghcqbQLMQa/+pSxFMZgYSIGWL0TCmcUxKiwpyhGSsCP5whhCrss7UC8IKFkBFIiL2YIcbOFPn+vhWCysrC+qCrqipwjA370BcCT23lCSFkBE+InRkiLikUIC6cBRQoiaoiKaoVjld1XdYlNCBQU9Iz6TX4q3UixO4MEYUYA3SdJwpnBRXKerWq2YmqXJV4gOWSX6vrivNFoZ0QZAQ+GLUjxAzx34Bw8qU/P9QlQPytEuVyVdbLPqu6ABtdhwEywmCXwV5EIUaAC2e9RDqo2QHsas4Myz5wxApBWSLD3WTZelAIuf5ozBBjgcN5koQg2AHZeyminVC04tY0yzIjIwxniFhDjAQXTlFA2CIH6kwuNLnnGBZi0t1jhhgJuFbkDiH8iYPrSwJK8AgxQ/w/nNwqxIo3oG7bUd5U0IFeqtqWg0cYzBCxhhgVLpxlTQVC5SgZrhmoxLSQInZnhdCoK90Iscv4TzhJBQCoakbi388WTHdQsw4qQ5chI7AQ8TzE2DnJb2/pMlYMPdlSXfJ8ARtsk2FkhIEMwVZEIUaBC2e5dIgWvhM1Y33IjVksGhnB+z6EqBAzxHjohKDqsc0ORA36TnB5yXDTmaf8uwxPiKHfdn6dRyFCx4WzsvVDK0QNSuCkaBXpuk31Tin+WuWAEPLrb84S/OvvKETwiBAU7Kp0NlR8yDUmPwj+HNQKkStlFr4QMGJwfYg4ZQRPN2WUWuUaCiD2bSZA8Et37oH90AzvlFbNsBD+CjKx7RwDXYYodZ4pBSVAOzFAD3dOElvtfIA5Jle0VoQnxNAaU6diUTkGXDiLQmVpBiEsWmWmUQX0gAIO3ZIZdJzwwRNicBW62GWMAhdOXeTpIs3b6OdmQb/dbMCiyZAqekZwj7FDCH+dyijEKHDhVDrHL/nSNkVo89guDQIpDL/W90HbBOELsXsl2yhE2GxkCCjwOM0oSWgkAHddX5PlbISm1/kEZUpr5rMP8MVrO3etdR2FCJtOCGWSx5QPckUJooEdgJSAEErpruHMFnZ5KetL4wmxazX8KETYdFNGbtcLMYYyQcoBZymSjL91T1vNDUa3NCHwT13H62WMHBfOtXErm+daGV67FkAKw0KgrUDuqHTO3SZorxLfFyJeUWf8uHA26cIK8cAucuyEeECTiGoeL7DPZ0WWov348vr1bH/2uoARXoaI19waORtCwIXNhWxZD24+04YmB7Sf+2Wl1+SN0a+1zhNPCBjxe1fli0KEigiBmpINkHUHGXJhgU2SmuX+/gypIamXq/0ZncNME1+I37tuZxQiVDoh2pWNubPwWIB0OStmJl2VaZqWs/3abKkhfuvKvlGIUJEMwZda64Nywl5gCzpks9qYfVXCiWxVF2UxW1b5r4TwmfgvRSFChcJJ8UwNgBQpwIahQ9Oj3p9lxhTa6JJWNO2EmA4IAaIQ48GFM2sxdtuR4UbH9JJeoYiochgxQy1RwZefhQAuI0wGH3FZwlBBOE+zEO/evcs6DN0d7ERerGazusxRPeyvMqNnlTFOCPkaPgvxO0rExc9DpSeESVMjIvT8oJNTq+Vsf5lnqqyq/WW9qtLUEwJ4dYPPJAoRLCcvOyFAtsE7t3vXPocqJbJDboyqytWyUNAnbWSEi5PJ5AQhUae7t3eciEKEiAjBsADWgw6ZOExWzfZLY1JT1MZsEwLsrieJ+OFWgHA4EU8joYcLgtNEMoeGGPWyNECEmDoh+vQN8NmLQoRGJ8SmBtrJYW1wNzd9gFlFPnhC2NDvdTbgYXdC72gShQgNDifi+Yu0YKwkftLAscrYh6SRESAE0yse7ZFshQkRhQgODif4bGsEOODT9aD2iF5gG8BLGeHt5I+4ciASEnaFqDs3Tn5uq0kh65WZRhpSA1iH5s3xjRHenhKEu6cGOX/8QCQkKJyn71y/cePGHceNHtd7nOtx+sihn0a44W4b9P+jP0IkJKaIJoXstuMWc7PlQsvVljOb3EY0/3KE6ENo4P19Hdy+JcHkWN66easL5QUvkswtRPMvR4g+BMe0jWaLvKsllswZP5oHD/31CNGH4Jhidr9NUfQCuTWO1/hxG9H8yxGiDwEyRZZ3YRyY7q/9zE1E8y9HiD6EyJFzt3uxlEj6ofSj+fsjRB9GwtGDp2+AgU7TaxnppYNH/8EIkUgkbH4Anl4J/prSme0AAAAASUVORK5CYII=";
    }
}


function startGame(gameId,gameStatus) {
    var game = new Phaser.Game({
        type: Phaser.AUTO,
        parent: "game-mahjong",
        transparent: true,
        scale: {
            mode: Phaser.Scale.FIT,
            autoCenter: Phaser.Scale.CENTER_BOTH,
            width: 1024,
            height: 960
        },
        scene: [new Preloader(), new Main(gameId,gameStatus)]
    });
}

function saveGame(gameId,gameStatus) {
    $("#game-pairs").html(gameStatus.remainingCount);
    console.log("Save gameId:",gameId, "Valid pairs left: ", gameStatus.remainingCount, " Tiles: ",gameStatus.remainingTiles);
}