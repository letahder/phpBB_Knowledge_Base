<?php

namespace letahder\knowledgebase\migrations\v10x;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['knowledge_base_mod_version']) && version_compare($this->config['knowledge_base_mod_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'kb_articles' => array(
					'COLUMNS' => array(
						'article_id'			=> array('UINT', NULL, 'auto_increment'),
						'category_id'			=> array('UINT', 0),
						'article_approved'		=> array('BOOL', 1),
						'article_denied'		=> array('BOOL', 0),
						'article_title'			=> array('VCHAR', ''),
						'article_description'	=> array('VCHAR', ''),
						'article_poster'		=> array('UINT', 0),
						'article_time'			=> array('TIMESTAMP', 0),
						'enable_bbcode'			=> array('BOOL', 1),
						'enable_smilies'		=> array('BOOL', 1),
						'enable_magic_url'		=> array('BOOL', 1),
						'message'				=> array('MTEXT', ''),
						'bbcode_bitfield'		=> array('VCHAR', ''),
						'bbcode_uid'			=> array('VCHAR:8', ''),
					),
					'PRIMARY_KEY' => 'article_id',
				),

				$this->table_prefix . 'kb_categories' => array(
					'COLUMNS' => array(
						'category_id'	=> array('UINT', NULL, 'auto_increment'),
						'left_id'		=> array('UINT', 0),
						'right_id'		=> array('UINT', 0),
						'category_name'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY' => 'category_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'kb_articles',
				$this->table_prefix . 'kb_categories',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('u_kb_delete')),
			array('permission.add', array('u_kb_edit')),
			array('permission.add', array('u_kb_post')),

			array('permission.add', array('m_kb_approve')),
			array('permission.add', array('m_kb_delete')),
			array('permission.add', array('m_kb_deny')),
			array('permission.add', array('m_kb_disapprove')),
			array('permission.add', array('m_kb_edit')),

			array('permission.add', array('a_kb_manage')),

			array('module.add', array(
				'acp',
				0,
				'ACP_KNOWLEDGE_BASE_TITLE'
			)),

			array('module.add', array(
				'acp',
				'ACP_KNOWLEDGE_BASE_TITLE',
				'ACP_KNOWLEDGE_BASE_CATEGORIES'
			)),

			array('module.add', array(
				'acp',
				'ACP_KNOWLEDGE_BASE_CATEGORIES',
				array(
					'module_basename'	=> '\letahder\knowledgebase\acp\main_module',
					'modes'				=> array('categories'),
				),
			)),

			array('config.add', array('knowledge_base_mod_version', '1.0.0')),
		);
	}
}
