const puppeteer = require('puppeteer');

// Get CLI args (borough, house number, and street)
const [,, borough, houseNumber, streetName] = process.argv;

if (!borough || !houseNumber || !streetName) {
  console.error('Usage: node scrape_bis.js <borough> <houseNumber> <streetName>');
  process.exit(1);
}

const scrapeLegalAdultUse = async (borough, houseNumber, streetName) => {
  const url = 'https://a810-bisweb.nyc.gov/bisweb/bispi00.jsp';

  const browser = await puppeteer.launch({
    headless: false,
    slowMo: 50,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-web-security',  // Disable web security to bypass CSP
      '--disable-features=IsolateOrigins,site-per-process' // Disable site isolation
    ]
  });

  const page = await browser.newPage();

  try {
    // Set a custom user agent
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    // Block analytics and other non-essential resources
    await page.setRequestInterception(true);
    page.on('request', (request) => {
      if (['image', 'stylesheet', 'font', 'script'].includes(request.resourceType()) || request.url().includes('google-analytics')) {
        request.abort();
      } else {
        request.continue();
      }
    });

    console.log('Navigating to BIS...');
    await page.goto(url, { 
      waitUntil: ['domcontentloaded', 'networkidle0'],
      timeout: 180000 // Increased timeout to 3 minutes
    });

    console.log(`Filling form: Borough ${borough}, ${houseNumber} ${streetName}`);
    
    // Wait for form elements to be present
    await page.waitForSelector('#boro1', { timeout: 60000 });
    await page.waitForSelector('input[name="houseno"]', { timeout: 60000 });
    await page.waitForSelector('input[name="street"]', { timeout: 60000 });

    await page.select('#boro1', borough);
    await page.type('input[name="houseno"]', houseNumber);
    await page.type('input[name="street"]', streetName);

    await new Promise(resolve => setTimeout(resolve, 2000)); // Increased delay

    console.log('Submitting form...');
    
    // Click the submit button instead of form.submit()
    const submitBtn = await page.$('input[type="submit"]');
    if (!submitBtn) {
      throw new Error('Submit button not found');
    }
    
    // Click and wait for navigation
    await Promise.all([
      page.waitForNavigation({ 
        waitUntil: ['domcontentloaded', 'networkidle0'],
        timeout: 180000 // Increased timeout to 3 minutes
      }),
      submitBtn.click()
    ]);

    console.log('Page loaded, checking content...');

    // Capture any errors on the page
    const pageErrors = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('.error')).map(el => el.innerText);
    });
    if (pageErrors.length > 0) {
      console.error('Page errors:', pageErrors);
    }

    // Capture a screenshot for debugging
    await page.screenshot({ path: 'debug-screenshot.png' });

    const isLegalAdultUse = await page.evaluate(() => {
      const bodyText = document.body.innerText;
      return bodyText.includes('Legal Adult Use: YES');
    });

    await browser.close();
    // For Laravel shell_exec parsing
    console.log(isLegalAdultUse ? 'true' : 'false');
  } catch (err) {
    console.error('Scraping failed:', err.message);

    // Capture a screenshot on error
    await page.screenshot({ path: 'error-screenshot.png' });

    await browser.close();
    console.log('false');
    process.exit(1);
  }
};

scrapeLegalAdultUse(borough, houseNumber, streetName);
