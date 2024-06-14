/**
 * WordPress dependencies
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import { postCategories, button } from '@wordpress/icons';

import { addFilter } from "@wordpress/hooks";


/**
 * Internal dependencies
*/
import { TAX_VENUE_SHADOW, GPOOV_CLASS_NAME, VARIATION_OF } from './helpers/namespace';
import GPQLIcon from './components/icon';


import { onlineEventButtonEdit } from './edit';


/**
 * 
 * 
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */
registerBlockVariation( 'core/post-terms', {
	name: GPOOV_CLASS_NAME,
	title: __( 'Venue Archive Links', 'gatherpress' ),
	description: __( 'Displays links to the selected venues archive.', 'gatherpress' ),
	category: 'gatherpress',
	icon: GPQLIcon( postCategories ),
	isActive: [ 'term', 'className' ],
	// @source https://github.com/WordPress/gutenberg/issues/41303#issuecomment-1760985709 
	// I had to add blockAttrs to the fn to make this work, because the className only exists within the variationAttrs, which comes second.
	// isActive: ( blockAttrs, { className }) => {
	// 	return (
	// 		className.includes(GPOOV_CLASS_NAME) // check if className contains GPOOV_CLASS_NAME and not equals. incase you add additional css classes it will still work
	// 	);
	// },
	attributes: {
		// namespace: GPOOV_CLASS_NAME,
		className: GPOOV_CLASS_NAME,
		term:TAX_VENUE_SHADOW,

	},
	// allowedControls: [],
	// scope: [ 'inserter', 'transform', 'block' ], // Defaults to 'block' and 'inserter'.
	// example: {} // Disabled like the original 'core/post-terms' block.
} );


const GPOOV_VARIATION_ATTRIBUTES = {
	title: __( 'Online-Event Link', 'gatherpress' ),
	description: __( 'A button linking to the online-event or the venue.', 'gatherpress' ),
	category: 'gatherpress',
	icon: GPQLIcon( button ),
};

const GPOOV_BUTTON_ATTRIBUTES = {
	className: GPOOV_CLASS_NAME + '-button',
	// While <a> is semantically more accurat, button can be disabled.
	// By setting this to 'button', instead of 'a', we can completely prevent the LinkControl getting rendered into the Toolbar.
	// tagName: 'button',
	tagName: 'a',
	title: __( 'Visit Online-Event', 'gatherpress' ),
	text: '🌐 ' + __( 'Online-Event', 'gatherpress' ),
	metadata: {
		// Supported Attributes for 'core/button': url, text, linkTarget, rel
		// https://make.wordpress.org/core/2024/03/06/new-feature-the-block-bindings-api/
		bindings: {
			url: {
				source: "gatherpress/onlineevent-or-venue",
				args: {
					key: "url"
				}
			},
			// Works only if contains attribute has data, either from an editor or by default. BUT IT NEEDS SOME DATA !!
			text: {
				source: "gatherpress/onlineevent-or-venue",
				args: {
					key: "text"
				}
			}
		}
	},
	urlShorten: 20
};


/*  */
registerBlockVariation( VARIATION_OF, {
	...GPOOV_VARIATION_ATTRIBUTES,
	name: GPOOV_CLASS_NAME + '-button',
	isActive: [ 'className', 'metadata.bindings.url.source' ],
	// DO NOT ADD A CLASSNAME, IT PREVENT BLOCK-STYLES.
	isActive: [ 'metadata.bindings.url.source' ],
	// @source https://github.com/WordPress/gutenberg/issues/41303#issuecomment-1760985709 
	// I had to add blockAttrs to the fn to make this work, because the className only exists within the variationAttrs, which comes second.
	// isActive: ( blockAttrs, { className }) => {
	// 	return (
	// 		className.includes(GPOOV_CLASS_NAME) // check if className contains GPV_CLASS_NAME and not equals. incase you add additional css classes it will still work
	// 	);
	// },
	attributes: {
		...GPOOV_BUTTON_ATTRIBUTES,
	},
	scope: [ 'inserter', 'transform', 'block' ], // Defaults to 'block' and 'inserter'.
	example: {}
} );


/**
 * A Trap block, that looks like a single button, hohoho.
 *  
 * This block-variation is only useful, because a user can pick the block directly from the inserter or the left sidebar.
 * 
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */
registerBlockVariation( 'core/buttons', {
	...GPOOV_VARIATION_ATTRIBUTES,
	name: 'pseudo-' + GPOOV_CLASS_NAME + '-button',
	// isActive: [ 'namespace', 'title' ], // This is not used/disabled by purpose.
	innerBlocks: [
		[
			VARIATION_OF,
			{
				...GPOOV_BUTTON_ATTRIBUTES
			},

		],
	],
	example: {
		innerBlocks: [
			{
				name: VARIATION_OF,
				attributes: {
					...GPOOV_BUTTON_ATTRIBUTES
				}
			},
		]
	}
} );




/**
 * Add the edit component to the block.
 * This is the component that will be rendered in the editor.
 * It will be rendered after the original block edit component.
 *
 * @param {function} BlockEdit Original component
 * @returns {function} Wrapped component
 *
 * @see https://developer.wordpress.org/block-editor/developers/filters/block-filters/#editor-blockedit
 */
addFilter(
	'editor.BlockEdit',
	'gatherpress-onlineevent-or-venue/button-block-variation',
	onlineEventButtonEdit,
	1
);







addFilter(
    'blocks.registerBlockType',
	'gatherpress-onlineevent-or-venue/extend-button-block',
    extendButtonBlock
);

function extendButtonBlock(settings, name) {
    if (name !== VARIATION_OF) {
        return settings;
    }

	const newSettings = {
        ...settings,
		attributes: {
			...settings.attributes,
			urlShorten: { 
				type: 'integer',
			},
		},
        supports: {
            ...settings.supports,
			className: false, // Removes "Additional CSS classes" panel for blocks that support it
			// customClassName: false // **Updated** For blocks that don't have className
        },
    }
	// console.log(newSettings);
	return newSettings;
}
