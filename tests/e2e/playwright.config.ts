import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    // This directory holds all the test files.
    // https://playwright.dev/docs/api/class-testconfig#test-config-test-dir
    //
    // IDEA: Maybe this should be set to "../../src/"
    // where the test files would be housed directly with their components, blocks, etc.
    testDir: 'tests',
    // This is run before any tests. Check the file for more information.
    globalSetup: 'global-setup.ts',
    use: {
        // It's simpler to use relative paths when referencing our application's URLs.
        // https://playwright.dev/docs/test-webserver#adding-a-baseurl
        baseURL: process.env.WP_BASE_URL,
        // We save as much information as possible to make debugging easier.
        // https://playwright.dev/docs/api/class-testoptions#test-options-screenshot
        // https://playwright.dev/docs/api/class-testoptions#test-options-trace
        // https://playwright.dev/docs/api/class-testoptions#test-options-video
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        video: 'retain-on-failure',
    },
	// Configure projects for major browsers
    // We can test on different or multiple browsers if needed.
    // https://playwright.dev/docs/test-projects#configure-projects-for-multiple-browsers
    projects: [
		// {
		//   name: "chromium",
		//   use: { ...devices["Desktop Chrome"] },
		// },

		{
			name: 'firefox',
			use: { ...devices['Desktop Firefox'] },
		},

		{
			name: 'webkit',
			use: { ...devices['Desktop Safari'] },
		},

		/* Test against mobile viewports. */
		// {
		//   name: 'Mobile Chrome',
		//   use: { ...devices['Pixel 5'] },
		// },
		// {
		//   name: 'Mobile Safari',
		//   use: { ...devices['iPhone 12'] },
		// },

		/* Test against branded browsers. */
		// {
		// 	name: 'Microsoft Edge',
		// 	use: { ...devices['Desktop Edge'], channel: 'msedge' },
		// },
		{
			name: 'Google Chrome',
			use: { ...devices['Desktop Chrome'], channel: 'chrome' },
		},
	],
    // Locally, we could take advantage of parallelism due to multicore systems.
    // However, in the CI, we typically can use only one worker at a time.
    // It's more straightforward to align how we run tests in both systems.
    // https://playwright.dev/docs/test-parallel
    workers: 1,
});