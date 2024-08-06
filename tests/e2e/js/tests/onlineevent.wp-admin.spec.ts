import { test, expect } from '@test-utils';

// We have multiple tests in this file, all requiring us to be authenticated.
// Compare this to the front-end.spec.ts.
test.use({ storageState: process.env.WP_AUTH_STORAGE });

test.beforeAll(async ({ requestUtils }) => {
    await requestUtils.activatePlugin('gatherpress');
});

test.describe(() => {
    test('A link to the plugin settings page is present under the Events menu', async ({
        page,
        admin,
    }) => {
        await admin.visitAdminPage('/');

        const gatherPressMenuItem = page.locator('li', {
            has: page.getByRole('link', { name: 'Events' }),
        });
        const wpGatherPressSettingsItem = gatherPressMenuItem.getByRole('link', {
            name: 'Settings',
        });
        const wpGatherPressSettingsItemUrl =
            await wpGatherPressSettingsItem.getAttribute('href');

        await expect(wpGatherPressSettingsItem).toBeVisible();
        await expect(wpGatherPressSettingsItemUrl).toContain(
            'edit.php?post_type=gatherpress_event&page=gatherpress_general',
        );
    });

/*     test('A custom message can be set on the settings page', async ({
        page,
        admin,
        wpGatherPress,
    }) => {
        await wpGatherPress.deleteCustomMessage();

        await admin.visitAdminPage('options-general.php?page=wpgov_wpgb');

        const customMessageField = page.getByLabel('Top Bar Message');
        const saveButton = page.getByRole('button', { name: 'Save Changes' });

        await customMessageField.fill('Sic vita est!');
        await saveButton.click();

        // At this point, after the form submission, we are on a new page.
        const successMessage = page.getByText('Settings saved');

        await expect(successMessage).toBeVisible();
        await expect(customMessageField).toHaveValue('Sic vita est!');
    }); */
});