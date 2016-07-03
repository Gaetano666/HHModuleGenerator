HumHub Modules Generator
===============================

Installation
---------------------------

step-1. Install module in humhub/protected/modules
step-2. In humhub/protected/humhub/config/web.php

```
    'modules' =>[
    	....................
		'gii' => [
			'class'=>'yii\gii\Module',
			'generators' => [
					'hh' => [
							'class' => 'app\modules\generator\Generator',
							'templates' => ['defaults' => '@app/modules/generator/default']
					]
			],
			'allowedIPs'=>['127.0.0.1','*'],
		],
		....................
	],
```

step-3 Go to /index.php?r=gii 

step-4 Create a table into the humhub database that will store your data 

step-5 Create your module providing the input values required

step-6 Edit widgets\views\form.php to insert the values you need. 

step-7 Edit widgets\views\entry.php to generate the layout you prefer - the module generate all the fields available into the DB table, but with ugly layout :)

EDITING:
once the Module has been generated, you will need to customize some areas given
for the time being the generator is not able to know in advance which data you 
wish to show to user and how you would like to manage the data into the database

TRANSLATIONS/INTERNATIONALIZATION:
The module is not enabled to generate the "translations"; if you wish to include these feature, you need to change all the text strings; e.g.:
"My Text" -> Yii::t('ExampleModule.some_own_category', 'My text')
see https://www.humhub.org/docs/guide-dev-i18n.html

TO DO:
1) improve instructions within the generated files to help the users to complete
   the module (e.g. show.php, edit.php, and so on)
2) include reference to the HumHub guide for each section, where useful for user
3) Remove unnecessary code
4) Try to insert automatically the tables into the views/widgets (DONE)
5) Check all the pending "\\to do"