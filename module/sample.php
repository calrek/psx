<?php

use PSX\Module\ViewAbstract;

class sample extends ViewAbstract
{
	public function onLoad()
	{
		$this->getTemplate()->assign('title', 'PSX Framework');
		$this->getTemplate()->assign('subTitle', 'Template sample ...');
		$this->getTemplate()->set('sample.tpl');
	}
}
