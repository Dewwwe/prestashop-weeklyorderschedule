# Weekly Order Schedule Module for PrestaShop

This module allows you to control which days of the week customers can place orders on your store. On disabled days, customers can still browse products and add them to cart, but they won't be able to complete checkout as all carrier options will be hidden.


## Features

- Enable/disable ordering for each day of the week
- Customers can still browse products and build carts on disabled days
- Easy-to-use admin interface
- Compatible with PrestaShop 1.7+

## Installation

1. Download the latest release from the [Releases page](https://github.com/dewwwe/weeklyorderschedule/releases)
2. Go to your PrestaShop back office > Modules > Module Manager
3. Click "Upload a module"
4. Select the downloaded ZIP file
5. The module will install automatically

## Configuration

### Accessing the Configuration

There are two ways to access the module configuration:

1. Go to **Modules > Module Manager**, find "Weekly Order Schedule" and click "Configure"
2. Go to **Orders > Order Days** in your back office menu

### Setting Up Order Days

1. Enable the module using the main toggle switch
2. For each day of the week, set the toggle to:
   - **Enabled**: Customers can place orders on this day
   - **Disabled**: Customers cannot place orders on this day
3. Click "Save" to apply your changes

## How It Works

When a customer reaches the checkout page:

1. The module checks the current day of the week
2. If the current day is enabled, checkout proceeds normally
3. If the current day is disabled, all carrier options are hidden
4. Without carrier options, customers cannot proceed to payment
5. Customers can still browse products and add them to cart for later checkout

## Use Cases

- **Delivery Preparation**: Disable ordering on days before your delivery preparation
- **Weekend Management**: Disable weekend orders if you don't process them until Monday
- **Holiday Planning**: Disable specific days when you know you'll be closed
- **Inventory Management**: Block orders on days when you do inventory counts

## Troubleshooting

**Orders still possible on disabled days**
- Make sure the module is enabled (main toggle switch)
- Check that the specific day is properly disabled
- Clear your PrestaShop cache (Advanced Parameters > Performance > Clear cache)
- Test with a different browser or in incognito mode

**Module configuration not saving**
- Check your PrestaShop permissions
- Ensure your server has write access to the configuration files
- Try clearing your browser cache

## Contributing

We welcome contributions to improve this module!

### Development Setup

1. Clone this repository to your PrestaShop modules directory
2. Make your changes
3. Test thoroughly
4. Submit a pull request with a clear description of your changes

### Coding Standards

- Follow PrestaShop's coding standards
- Keep compatibility with PrestaShop 1.7+
- Document your code
- Add appropriate comments

## Disclaimer

This module is provided as-is. While we strive to maintain quality and functionality, we cannot guarantee perfect operation in all environments or with all PrestaShop versions. Always test thoroughly in a staging environment before deploying to production.

We are not affiliated with or endorsed by PrestaShop SA.

## License

This module is released under the [Academic Free License 3.0](https://opensource.org/licenses/AFL-3.0)

## Support

For issues, questions, or feature requests, please [open an issue](https://github.com/dewwwe/weeklyorderschedule/issues) on GitHub.

---

## Changelog

### 1.0.0 (YYYY-MM-DD)
- Initial release
- Basic day-of-week restriction functionality
- Admin interface for configuration

---

*Remember to replace "yourusername" with your actual GitHub username and update the screenshot path if needed.*
