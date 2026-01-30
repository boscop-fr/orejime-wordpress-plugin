import { createBlock, registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';
import save from './save';

registerBlockType( metadata.name, {
	edit,
	save,
	transforms: {
		// @see core/group transforms
		from: [
			{
				type: 'block',
				isMultiBlock: true,
				blocks: [ '*' ],
				__experimentalConvert( blocks ) {
					const innerBlocks = blocks.map( ( block ) =>
						createBlock(
							block.name,
							block.attributes,
							block.innerBlocks
						)
					);

					return createBlock( metadata.name, {}, innerBlocks );
				},
			},
		],
		// @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-transforms/#ungroup-blocks
		ungroup: ( attributes, innerBlocks ) =>
			innerBlocks.flatMap( ( innerBlock ) => innerBlock.innerBlocks ),
	},
} );
