import fs from 'node:fs';
import { basename } from 'node:path';
import pkg from 'orejime/package.json' with { type: 'json' };

const SOURCE = './node_modules/orejime/dist';
const DEST = './public';

// Copies scripts and styles from the orejime module to the
// plugin folder.
fs.rmSync( DEST, { recursive: true, force: true } );
fs.cpSync( SOURCE, DEST, { recursive: true } );

// Lists available languages.
const langs = fs
	.readdirSync( DEST )
	.filter( ( file ) => file.startsWith( 'orejime-standard-' ) )
	.map( ( file ) => basename( file, '.js' ).slice( -2 ) );

// Writes info about the lib to be consumed by the plugin.
const manifest = `<?php
return array(
	'version' => '${ pkg.version }',
	'langs'   => array( '${ langs.join( "', '" ) }' ),
);
`;

fs.writeFileSync( DEST + '/orejime-manifest.php', manifest );
