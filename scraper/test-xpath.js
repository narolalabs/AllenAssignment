const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  await page.goto('https://example.com', { waitUntil: 'domcontentloaded' });

  const elements = await page.evaluate(() => {
    const xpath = '//h1';
    const result = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
    return result.snapshotLength;
  });
  console.log('XPath Works! Found:', elements);

  await browser.close();
})();
