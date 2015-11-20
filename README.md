magento-devtools
================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/razvan-mocanu/magento-devtools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/razvan-mocanu/magento-devtools/?branch=master)  
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5ce5a6ca-4c05-44b7-b188-ca3ef680b0de/big.png)](https://insight.sensiolabs.com/projects/5ce5a6ca-4c05-44b7-b188-ca3ef680b0de)

RazvanMocanu DevTools

1. What is it

This module wraps each block in comment tags and ads information about the block. The available information is: name, template and data. This allows the developer to quickly identify blocks and find the associated templates and data. It doesn't work as the template hints. All the information is contained inside the HTLM which you can inspect with the browser. If you are using Firebug, don't forget to set it to show comments. See screenshots. It works with C.E. and E.E.

Works with both CE and EE.
Developed on EE 1.14, used and tested also on CE 1.9

2. Versions

V.0.5

Code reorganisation.

V.0.6

- CMS block/page info
- repositioning root block info after the <!DOCTYPE HTML>
- layout update handles list
- some php doc added

V.1.0.1

Refactored.

V.1.0.2

- Included functionality for Admin area
