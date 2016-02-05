/**
 * @author gravgun
 * @see http://www.blitzbasic.com/sdkspecs/sdkspecs/b3dfile_specs.txt
 */

THREE.B3DLoader = function (manager) {
	this.manager = (manager !== undefined) ? manager : THREE.DefaultLoadingManager;
};

THREE.B3DLoader.prototype = {
	constructor: THREE.B3DLoader,

	load: function (url, onLoad, onProgress, onError) {
		var scope = this;

		var loader = new THREE.XHRLoader(scope.manager);
		loader.setCrossOrigin(this.crossOrigin);
		loader.setResponseType('arraybuffer');
		loader.load(url, function (buffer) {
			onLoad(scope.parse(buffer));
		}, onProgress, onError);
	},

	setCrossOrigin: function (value) {
		this.crossOrigin = value;
	},

	parse: ( function () {
		return function (buffer) {
			var txt4cc = function (val) {
				return String.fromCharCode(val >> 24, val >> 16 & 0xFF, val >> 8 & 0xFF, val & 0xFF);
			};
			var bin4cc = function (val) {
				return (val.charCodeAt(0) << 24) + (val.charCodeAt(1) << 16) + (val.charCodeAt(2) << 8) + val.charCodeAt(3);
			};
			var readCString = function (data, offset) {
				var c, s = [], o = 0;
				while (true) {
					c = data.getUint8(offset + o);
					o++;
					if (c != 0) {
						s.push(c);
					} else {
						break;
					}
				}
				var encodedString = String.fromCharCode.apply(null, s);
				return [decodeURIComponent(escape(encodedString)), o];
			};

			console.time('B3DLoader');

			var data = new DataView(buffer);

			var header = data.getUint32(0);
			if (header != bin4cc('BB3D')) {
				console.error('Invalid B3D header (got 0x' + header.toString(16) + ', expected ' + bin4cc('BB3D') + ')');
				return;
			}

			var fileLength = data.getInt32(4, true);
			if (fileLength + 8 < data.byteLength) {
				console.error('B3D data too short (need ' + fileLength + 8 + ', have ' + data.byteLength + ')');
				return;
			}

			var version = data.getInt32(8, true);
			console.info('B3D file version ' + version/100);

			var geometry = new THREE.Geometry();
			var offset = 12;
			while (offset + 4 < data.byteLength) {
				var chunkName = txt4cc(data.getUint32(offset)); offset += 4;
				var chunkLength = data.getInt32(offset, true); offset += 4;
				var eoc = offset + chunkLength;
				switch (chunkName) {
				case 'TEXS':
					while (offset < eoc) {
						var [texFileName, texFileNameLen] = readCString(data, offset); offset += texFileNameLen;
						var flags = data.getInt32(offset, true); offset += 4;
						var blend = data.getInt32(offset, true); offset += 4;
						var pos = {x: data.getFloat32(offset, true), y: data.getFloat32(offset+4, true)}; offset += 8;
						var scale = {x: data.getFloat32(offset, true), y: data.getFloat32(offset+4, true)}; offset += 8;
						var rotation = data.getFloat32(offset, true); offset += 4;
						// add to texture array?
					}
					break;
				case 'BRUS':
					var nTexs = data.getInt32(offset, true); offset += 4;
					while (offset < eoc) {
						var [texName, texNameLen] = readCString(data, offset); offset += texNameLen;
						var color = {
							r: data.getFloat32(offset, true),
							g: data.getFloat32(offset + 4, true),
							b: data.getFloat32(offset + 8, true),
							a: data.getFloat32(offset + 12, true)
						}; offset += 16;
						var shininess = data.getFloat32(offset, true); offset += 4;
						var blend = data.getInt32(offset, true); offset += 4;
						var fx = data.getInt32(offset, true); offset += 4;
						var texIds = new Int32Array(nTexs);
						for (var i=0; i < nTexs; ++i) {
							texIds[i] = data.getInt32(offset, true); offset += 4;
						}
						// add to brush array?
					}
					console.warn('BRUS');
					break;
				case 'VRTS':
					var flags = data.getInt32(offset, true); offset += 4;
					var texCoordSets = data.getInt32(offset, true); offset += 4;
					var texCoordSetSize = data.getInt32(offset, true); offset += 4;
					while (offset < eoc) {
						var x = data.getFloat32(offset, true),
						    y = data.getFloat32(offset+4, true),
						    z = data.getFloat32(offset+8, true),
						    nx = data.getFloat32(offset+12, true),
						    ny = data.getFloat32(offset+16, true),
						    nz = data.getFloat32(offset+20, true); offset += 24;
						var color = {
							r: data.getFloat32(offset, true),
							g: data.getFloat32(offset + 4, true),
							b: data.getFloat32(offset + 8, true),
							a: data.getFloat32(offset + 12, true)
						}; offset += 16;
						// TODO add to vertex array
					}
					console.warn('VRTS');
					break;
				case 'NODE':
					console.warn('NODE');
					break;
				default:
					//console.warn('!' + chunkName);
					break;
				}
			}
/*
			//

			var geometry = new THREE.Geometry();

			// uvs

			var uvs = [];
			var offset = header.offset_st;

			for ( var i = 0, l = header.num_st; i < l; i ++ ) {

				var u = data.getInt16( offset + 0, true );
				var v = data.getInt16( offset + 2, true );

				uvs.push( new THREE.Vector2( u / header.skinwidth, 1 - ( v / header.skinheight ) ) );

				offset += 4;

			}

			// triangles

			var offset = header.offset_tris;

			for ( var i = 0, l = header.num_tris; i < l; i ++ ) {

				var a = data.getUint16( offset + 0, true );
				var b = data.getUint16( offset + 2, true );
				var c = data.getUint16( offset + 4, true );

				geometry.faces.push( new THREE.Face3( a, b, c ) );

				geometry.faceVertexUvs[ 0 ].push( [
					uvs[ data.getUint16( offset + 6, true ) ],
					uvs[ data.getUint16( offset + 8, true ) ],
					uvs[ data.getUint16( offset + 10, true ) ]
				] );

				offset += 12;

			}

			// frames

			var translation = new THREE.Vector3();
			var scale = new THREE.Vector3();
			var string = [];

			var offset = header.offset_frames;

			for ( var i = 0, l = header.num_frames; i < l; i ++ ) {

				scale.set(
					data.getFloat32( offset + 0, true ),
					data.getFloat32( offset + 4, true ),
					data.getFloat32( offset + 8, true )
				);

				translation.set(
					data.getFloat32( offset + 12, true ),
					data.getFloat32( offset + 16, true ),
					data.getFloat32( offset + 20, true )
				);

				offset += 24;

				for ( var j = 0; j < 16; j ++ ) {

					var character = data.getUint8( offset + j, true );
					if( character === 0 ) break;
					
					string[ j ] = character;

				}

				var frame = {
					name: String.fromCharCode.apply( null, string ),
					vertices: [],
					normals: []
				};

				offset += 16;

				for ( var j = 0; j < header.num_vertices; j ++ ) {

					var x = data.getUint8( offset ++, true );
					var y = data.getUint8( offset ++, true );
					var z = data.getUint8( offset ++, true );
					var n = normals[ data.getUint8( offset ++, true ) ];

					var vertex = new THREE.Vector3(
						x * scale.x + translation.x,
						z * scale.z + translation.z,
						y * scale.y + translation.y
					);

					var normal = new THREE.Vector3( n[ 0 ], n[ 2 ], n[ 1 ] );

					frame.vertices.push( vertex );
					frame.normals.push( normal );

				}

				geometry.morphTargets.push( frame );

			}

			// Static

			geometry.vertices = geometry.morphTargets[ 0 ].vertices;

			var morphTarget = geometry.morphTargets[ 0 ];

			for ( var j = 0, jl = geometry.faces.length; j < jl; j ++ ) {

				var face = geometry.faces[ j ];

				face.vertexNormals = [
					morphTarget.normals[ face.a ],
					morphTarget.normals[ face.b ],
					morphTarget.normals[ face.c ]
				];

			}


			// Convert to geometry.morphNormals

			for ( var i = 0, l = geometry.morphTargets.length; i < l; i ++ ) {

				var morphTarget = geometry.morphTargets[ i ];
				var vertexNormals = [];

				for ( var j = 0, jl = geometry.faces.length; j < jl; j ++ ) {

					var face = geometry.faces[ j ];

					vertexNormals.push( {
						a: morphTarget.normals[ face.a ],
						b: morphTarget.normals[ face.b ],
						c: morphTarget.normals[ face.c ]
					} );

				}

				geometry.morphNormals.push( { vertexNormals: vertexNormals } );

			}

			geometry.animations = THREE.AnimationClip.CreateClipsFromMorphTargetSequences( geometry.morphTargets, 10 )

			console.timeEnd( 'B3DLoader' );

			return geometry;*/

		}

	} )()

}
