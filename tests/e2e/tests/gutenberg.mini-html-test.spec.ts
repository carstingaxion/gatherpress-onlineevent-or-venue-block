/**
 * REPLICA TO DEBUG
 * https://github.com/WordPress/gutenberg/blob/c48075b6665ec3910d00677088672c1ba9e24916/test/e2e/specs/editor/blocks/html.spec.js#L36
 */


/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );
import { WP_AUTH_STORAGE } from '@test-utils';

test.describe( 'HTML block', () => {
	test.beforeEach( async ( { admin } ) => {
		await admin.createNewPost();
	} );
    test.use({ storageState: WP_AUTH_STORAGE });
    
	test( 'can be created by typing "/html"', async ( { editor, page } ) => {
		// Create a Custom HTML block with the slash shortcut.
		await editor.canvas
			.getByRole( 'button', { name: 'Add default block' } )
			.click();
		await page.keyboard.type( '/html' );
		await expect(
			page.locator( 'role=option[name="Custom HTML"i][selected]' )
		).toBeVisible();
	} );

} );