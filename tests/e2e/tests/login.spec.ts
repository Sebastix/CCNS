import { test, expect } from '@playwright/test';

test.describe('Drupal Login Page', () => {
  test('should load login page', async ({ page }) => {
    // Navigate to the login page
    await page.goto('/user/login');
    
    // Verify the login form elements are present
    await expect(page.getByLabel('Username')).toBeVisible();
    await expect(page.getByLabel('Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Log in' })).toBeVisible();
  });

  test('should show error message with invalid credentials', async ({ page }) => {
    await page.goto('/user/login');
    
    // Try to login with invalid credentials
    await page.getByLabel('Username').fill('test-user');
    await page.getByLabel('Password').fill('wrong-password');
    await page.getByRole('button', { name: 'Log in' }).click();
    
    // Verify error message appears
    await expect(page.getByText('Unrecognized username or password')).toBeVisible();
  });
});