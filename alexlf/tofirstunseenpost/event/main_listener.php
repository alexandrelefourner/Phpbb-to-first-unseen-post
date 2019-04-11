<?php
/**
 *
 * To Last Unread Post. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Alexandre A. LE FOURNER
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace alexlf\tofirstunseenpost\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

 
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_template_vars' => 'modify_end_link',
			'core.display_forums_modify_sql' => 'change_display_sql',
			'core.display_forums_modify_forum_rows' => 'change_display_forums_modify_forum_rows',
			'core.viewforum_modify_topicrow' => 'viewforum_modify_topicrow'
		);
	}

	/* @var \phpbb\controller\helper */
	protected $helper;
	
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user               $user       User object
	 * @param string                    $php_ext    phpEx
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $php_ext,\phpbb\config\config $config)
	{
		$this->helper   = $helper;
		$this->template = $template;
		$this->user     = $user;
		$this->php_ext  = $php_ext;
		$this->config  = $config;
	}


	public function viewforum_modify_topicrow($event){
		//Change the topic link in the forum view.
		if($event["topic_row"]["S_UNREAD_TOPIC"]){
			$vd = $event["topic_row"];
			$vd["U_LAST_POST"] = $vd["U_NEWEST_POST"];
			$event["topic_row"] = $vd;
		}
	}
	
	
	public function change_display_forums_modify_forum_rows($event){
			//Save the topic_id for future use.
			$parent_id = $event["parent_id"];
			$forum_rows = $event["forum_rows"];
			$row =  $event["row"];
			if($row['forum_last_post_time'] == $forum_rows[$parent_id]['forum_last_post_time']){
				$forum_rows[$parent_id]["last_topic_id"] = $row["last_topic_id"];
			}
			$event["forum_rows"] = $forum_rows;
	}
	
	public function change_display_sql($event){
		//Change SQL request
			$sql_arr = $event["sql_ary"];
			$sql_arr["LEFT_JOIN"][sizeof($sql_arr["LEFT_JOIN"])] = array(
					'FROM'	=> array(POSTS_TABLE => 'fp'),
					'ON'	=> "f.forum_last_post_id = fp.post_id"
				);
			$sql_arr["SELECT"] .= ", fp.topic_id as last_topic_id";
			$event["sql_ary"] = $sql_arr;
		
	}
		
	
	public function modify_end_link($event){
		//Modify the link which will be displayed to the user.
		if($event["forum_row"]["S_UNREAD_FORUM"]){
			$frow = $event["forum_row"];
			$frow["U_LAST_POST"] = "./viewtopic.php?t=".$event["row"]["last_topic_id"]."&view=unread#unread";
			$event["forum_row"] = $frow;
		}
	}
	
}
