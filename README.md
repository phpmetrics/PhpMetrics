# PhpMetrics


[![License](https://poser.pugx.org/phpmetrics/phpmetrics/license.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Build Status](https://secure.travis-ci.org/phpmetrics/PhpMetrics.svg)](http://travis-ci.org/phpmetrics/PhpMetrics)
[![Latest Stable Version](https://poser.pugx.org/phpmetrics/phpmetrics/v/stable.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Slack](https://img.shields.io/badge/slack/phpmetrics-yellow.svg?logo=slack)](https://join.slack.com/t/phpmetrics/shared_invite/enQtODU3MjQ4ODAxOTM5LWRhOGFhODMxN2JmMDRmOGVjNGQ0ZjNjNzVlNDIwNzQ2MWQ2YzgxYmRlNmM5NzIzZjlhYTFjZjZhYzAyMjM0YmE)



![Standard report](https://github.com/phpmetrics/PhpMetrics/raw/master/doc/overview.png)


<img src="https://phpmetrics.org/imagesmetrics-maintenability.png" height="80px" alt="PhpMetrics" align="left" style="margin-right:20px"/>

PhpMetrics provides metrics about PHP project and classes, with beautiful and readable HTML report. 

<b>[Demo](http://www.phpmetrics.org/report/latest/index.html)</b> | [Documentation](http://www.phpmetrics.org/documentation/index.html) | [Twitter](https://twitter.com/Halleck45) | [Contributing](https://github.com/phpmetrics/PhpMetrics/blob/master/doc/contributing.md)

<br/><br/>


## Quick start

    composer require phpmetrics/phpmetrics --dev
    php ./vendor/bin/phpmetrics --report-html=myreport .
    
Then open the generated `./myreport/index.html` file in your browser. 

## More

If want, you can [install](https://github.com/phpmetrics/PhpMetrics/blob/master/doc/installation.md) PhpMetrics globally with your favorite package manager. You can also visit our [documentation](http://www.phpmetrics.org/documentation/index.html).

## Metrics list

See the [metrics](doc/metrics.md) file.

## Author

+ Jean-François Lépine <[@Halleck45](https://twitter.com/Halleck45)>

## License

See the [LICENSE](LICENSE) file.

## Contributing

See the [CONTRIBUTING](doc/contributing.md) file.
