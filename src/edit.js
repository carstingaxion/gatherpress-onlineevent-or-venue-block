/**
 * WordPress dependencies
 */

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Get stuff to filter block attributes on the fly
 *
 * @see https://github.com/WordPress/gutenberg/issues/10082#issuecomment-642786811
 */
import { createHigherOrderComponent } from '@wordpress/compose';

import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, Disabled } from '@wordpress/components';

import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import { OnlineEventButtonEdit } from './components/OnlineEventButtonEdit';
import { getCurrentContextualPostId } from './helpers/globals';

// import { isEventPostType } from './helpers/event';

import { PT_EVENT, PT_VENUE, TAX_VENUE_SHADOW, GPOOV_CLASS_NAME, VARIATION_OF } from './helpers/namespace';

const onlineEventButtonEdit = createHigherOrderComponent( ( BlockEdit ) => {
	return (props) => {

		if (props.name !== VARIATION_OF) {
			return <BlockEdit {...props} />;
		}

		if ( !props?.attributes?.className?.includes( GPOOV_CLASS_NAME + '-button' ) ) {
			return <BlockEdit {...props} />
		}

		const { isSelected } = props;

		const cId = getCurrentContextualPostId(props?.context?.postId) 
		// const post = useSelect(
		// 	(select) => select('core/editor').getCurrentPost(),
		// 	[]
		// );
		const [meta, setMeta] = useEntityProp(
			'postType',
			// post.type,
			'gatherpress_event',
			'meta',
			// post.id
			cId
		);

		const has_onlineevent_link = () => {
			return meta?.gatherpress_online_event_link || false
		}
		// Shorten the previewed button text,
		// to illustrate the setting to the editor
		const previewProps = {
			...props,
			attributes: {
				...props.attributes,
				text: props.attributes.text.substring(0, props.attributes.urlShorten )
			}
		}

		return (
			<>
				{ has_onlineevent_link() ?
					<>
						{ ! isSelected ?
							<BlockEdit {...props} />
						:
							<BlockEdit {...previewProps} />
						}
					</>
				: // Show as disabled & prevent interaction.
					<Disabled style={{ opacity: '35%' }}>
						<BlockEdit {...props} />
					</Disabled>
				}

				{/* { ! isDescendentOfQueryLoop && isSelected && ( */}
				{ isSelected && (
					<InspectorControls>
						<PanelBody
							title={__('onlineEventButtonEdit settings', 'gatherpress')}
							initialOpen={true}
						>
							<PanelRow>
								<OnlineEventButtonEdit {...props} />
							</PanelRow>
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	};
}, 'onlineEventButtonEdit' );

export { onlineEventButtonEdit };
