# Snowbabel TYPO3 CMS Extension

This repository is forked / imported from parent repository [snowflake](https://github.com/snowflakech/snowbabel). Last import was taken on **23rd May 2019**. From **24th May 2019** onwards we ([PIT SOLUTIONS](http://www.pitsolutions.ch)) will contribute our changes and works to this repository. We already done major release 6.0.0 version with removing the dependency of EXTJS from TYPO3 Backend Module and used Angular JS as an alternative. **Version 6.0.0** also support **TYPO3 8 LTS - 9 LTS**.

snowbabel provides an easy and modern way for Extension translation. Target groups are Content Managers and non-technical backend users.

Supports old fashion xml and new xliff since TYPO3 4.6.


## Installation

Use the package manager [composer](https://getcomposer.org/) to install foobar.

https://packagist.org/packages/pits/snowbabel

```bash
composer require pits/snowbabel:dev-master or specify version you want to install
composer require pits/snowbabel:6.0.0
```

## Functionality

A collection (comparison) of the main functionality of this product or of the different product versions/types. 

<table>
	<tr>
		<td></td>
		<td><strong>Available</strong></td>
	</tr>
	<tr>
		<td><strong>XLIFF & LLXML Support</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Easy to use</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Grid view</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Powerful search</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Paging</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Search for labels over your complete TYPO3 installation</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Check for new or deleted labels</strong></td>
		<td>x</td>
	</tr>
	<tr>
		<td><strong>Fully integrated rights management</strong></td>
		<td>x</td>
	</tr>
</table>

Requirements
-------------

 - TYPO3 6.2 LTS and above
 - static info tables

Installation
-------------

 - Add snowbabel extension
 - Run install tool db compare making sure tables are created
 - Run Upgrade Wizard
 - Configure snowbabel via 'Settings' module
 - Run indexing task

Configuration
-------------

 - Configure snowbabel via 'Settings' module
 - Go to 'Extensions' tab and add extensions to be translated
 - Go to 'Languages' tab and add languages to be available
 - Run indexing task

Additional configurations
-------------
There are several ways of customization

 - Define which group or user has access to what language or which extension

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
