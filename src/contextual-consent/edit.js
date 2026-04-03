import {
	BlockControls,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	MenuGroup,
	MenuItemsChoice,
	ProgressBar,
	ToolbarGroup,
	ToolbarDropdownMenu,
	Placeholder,
} from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { plugins } from '@wordpress/icons';

export default function ContextualConsentEdit( { attributes, setAttributes } ) {
	const { records: purposes, isResolving } = useEntityRecords(
		'taxonomy',
		'orejime_purpose',
		{ per_page: -1 }
	);

	return (
		<div { ...useBlockProps() }>
			<Placeholder label={ __( 'Contextual consent', 'orejime' ) }>
				<InnerBlocks />
			</Placeholder>

			<BlockControls>
				<ToolbarGroup>
					<ToolbarDropdownMenu
						label={ __( 'Purpose', 'orejime' ) }
						icon={ plugins }
						toggleProps={ {
							isBusy: isResolving,
						} }
					>
						{ ( { onClose } ) => (
							<MenuGroup label={ __( 'Purpose', 'orejime' ) }>
								{ isResolving ? (
									<ProgressBar />
								) : (
									<MenuItemsChoice
										choices={ purposes.map(
											( { id, name } ) => ( {
												label: name,
												value: id,
											} )
										) }
										value={ attributes.purposeId ?? '' }
										onSelect={ ( purposeId ) => {
											setAttributes( { purposeId } );
											onClose();
										} }
									/>
								) }
							</MenuGroup>
						) }
					</ToolbarDropdownMenu>
				</ToolbarGroup>
			</BlockControls>
		</div>
	);
}
