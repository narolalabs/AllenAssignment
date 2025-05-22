const puppeteer = require('puppeteer');

// Get CLI args (house number and street)
const [,, houseNumber, streetName] = process.argv;

if (!houseNumber || !streetName) {
  console.error('Usage: node scrape_bis.js <houseNumber> <streetName>');
  process.exit(1);
}

const scrapeLegalAdultUse = async (houseNumber, streetName) => {
  const url = 'https://a810-bisweb.nyc.gov/bisweb/bispi00.jsp';

  // Enable headless: false if you want to see it in action
  const browser = await puppeteer.launch({
    headless: false, // set false for debugging
    slowMo: 30,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();

  try {
    console.log('Navigating to BIS...');
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });

    console.log(`Filling form: ${houseNumber} ${streetName}`);
    await page.select('select[name="boro"]', '1'); // Manhattan = 1
    await page.type('input[name="houseno"]', houseNumber);
    await page.type('input[name="street"]', streetName);

    await new Promise(resolve => setTimeout(resolve, 500)); // small delay

    console.log('Submitting form...');
    const formSubmitted = await page.evaluate(() => {
      const form = document.querySelector('form[action="bispi01.jsp"]');
      if (form) {
        form.submit();
        return true;
      }
      return false;
    });

    if (!formSubmitted) {
      throw new Error('Form not found or failed to submit');
    }

    await page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 });
    console.log('Page loaded, checking content...');

    const isLegalAdultUse = await page.evaluate(() => {
      const bodyText = document.body.innerText;
      return bodyText.includes('Legal Adult Use: YES');
    });

    await browser.close();
    console.log('Scraping done:', isLegalAdultUse);
    // For Laravel shell_exec parsing
    console.log(isLegalAdultUse ? 'true' : 'false');
  } catch (err) {
    console.error('Scraping failed:', err.message);
    await browser.close();
    console.log('false');
    process.exit(1);
  }
};

scrapeLegalAdultUse(houseNumber, streetName);
