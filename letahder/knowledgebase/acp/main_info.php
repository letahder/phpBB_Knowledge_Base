<?php

namespace letahder\knowledgebase\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\letahder\knowledgebase\acp\main_module',
			'title'		=> 'ACP_KNOWLEDGE_BASE_TITLE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'categories'	=> array('title' => 'ACP_KNOWLEDGE_BASE_CATEGORIES', 'auth' => 'acl_a_kb_manage', 'cat' => array('ACP_KNOWLEDGE_BASE_TITLE')),
			),
		);
	}
}
