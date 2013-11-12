<?php
class pitch {
  private $time;
  private $db_table = 'pitches';
  private $_is_multiuser;
  
  function __construct() {
    global $username_url;
    
    $this->username_url = $username_url;
    $this->time = time();
  }
  
  function create($active_user,$tags,$similar_ideas,$article_to_grow,$reference_to_compare,$agencies,$companies,$studios,$explain,$top_line,$has_pitched,$who_has_seen,$partners,$coverphoto,$audience,  $in_a_tweet,$what_to_make,$behavior,$why_want,$pitched_to,$name_them,$experience,$generating_revenue,$end_user,$video_link,$link_to_articles,$trend_reports,$similar_projects,$submit) {



	// Categories:
	$categories = array();

	foreach($tags as $categoryid){
		$pitch_categories = db_single(db_q("SELECT * FROM pitch_categories WHERE pitch_categoryid='%d'", $categoryid));
		
		$categories[$categoryid] = $pitch_categories->name;
	}

	$serialized_tags = serialize($categories);


	$pitched_to = serialize($pitched_to);
	
	$generating_revenue = serialize($generating_revenue);

	// Feature Images:
    $image = $_FILES['coverphoto'];  

	#if( !$image['name'] ) return report("Please upload a cover photo.", 'coverphoto', $this->db_table);
	if( !$image['name'] ) relocate(url.'/pitch/share?error=Please upload a cover photo.');

	$file_name = uniqid('file_', false).'.jpg';
    $image = process_cropped_organic_image($image, $imageid, 'image', root.'media/', 260, 210, false, $file_name);

    if(is_object($image)) return $image;
    if(!$image) return report("Please upload a cover photo.", 'coverphoto', $this->db_table); 


	db_q(db_insert($this->db_table), $active_user,$similar_ideas,$article_to_grow,$reference_to_compare,$agencies,$companies,$serialized_tags,$studios,$explain,$top_line,1, time(), $imageid, $files, $has_pitched,$who_has_seen,$partners,$audience,$in_a_tweet,$what_to_make,$behavior,$why_want,$pitched_to,$name_them,$experience,$generating_revenue,$end_user,$video_link,$link_to_articles,$trend_reports,$similar_projects );

	$pitchid = db_last_id();
	
	$this->send_pitch_submission_confirmation($pitchid);
	
	if($submit == 'save') relocate(url.'/pitch/edit/'.$pitchid.'?success=success');
	
	else if ($submit == 'next') relocate(url.'/share/upload/'.$pitchid);
	
	//relocate(url.'/user/profile/?success=requesting');

  }
  

  
  function edit($active_user,$pitchid,$tags,$similar_ideas,$article_to_grow,$reference_to_compare,$agencies,$companies,$studios,$explain,$top_line,$has_pitched,$who_has_seen,$partners,$coverphoto,$audience,  $in_a_tweet,$what_to_make,$behavior,$why_want,$pitched_to,$name_them,$experience,$generating_revenue,$end_user,$video_link,$link_to_articles,$trend_reports,$similar_projects,$submit) {



	// Categories:  
	$categories = array();

	foreach($tags as $categoryid){
		$pitch_categories = db_single(db_q("SELECT * FROM pitch_categories WHERE pitch_categoryid='%d'", $categoryid));
		
		$categories[$categoryid] = $pitch_categories->name;
	}

	$serialized_tags = serialize($categories);


	$pitched_to = serialize($pitched_to);
	
	$generating_revenue = serialize($generating_revenue);

	//$check_cover_photo = db_single(db_q("SELECT coverphoto FROM pitches WHERE pitchid='%d'", $pitchid));
	
	// Feature Images:
    $image = $_FILES['coverphoto'];  
    
    if( !$image['name'] ) return report("Please upload a cover photo.", 'coverphoto', $this->db_table); 
        
	$file_name = uniqid('file_', false).'.jpg';
	$image = process_cropped_organic_image($image, $imageid, 'image', root.'media/', 260, 210, false, $file_name);

    if(is_object($image)) return $image;
    if(!$image) return report("Please upload a cover photo.", 'coverphoto', $this->db_table); 


	// TEXT input:
	$explain = htmlspecialchars($explain);

	$explain = mysql_real_escape_string($explain);


	db_q("update pitches set similar_ideas='%s', article_to_grow='%s', reference_to_compare='%s', agencies='%s', companies='%s', tags='%s', studios='%s', top_line='%s', coverphoto='%s', has_pitched='%s', who_has_seen='%s', partners='%s', audience='%s' where pitchid='%s'", $similar_ideas,$article_to_grow,$reference_to_compare,$agencies,$companies, $serialized_tags,$studios, $top_line, $imageid, $has_pitched,$who_has_seen,$partners,$audience, $pitchid);

	db_q("update pitches set in_a_tweet='%s', what_to_make='%s', behavior='%s', why_want='%s', pitched_to='%s', name_them='%s', experience='%s', generating_revenue='%s', end_user='%s', video_link='%s', link_to_articles='%s', trend_reports='%s', similar_projects='%s' where pitchid='%s'", $in_a_tweet,$what_to_make,$behavior,$why_want,$pitched_to,$name_them,$experience,$generating_revenue,$end_user,$video_link,$link_to_articles,$trend_reports,$similar_projects, $pitchid);



	
	if($submit == 'save') relocate(url.'/pitch/edit/'.$pitchid.'?success=success');
	
	else if ($submit == 'next') relocate(url.'/share/upload/'.$pitchid);
	

	relocate(url.'/pitch/edit/'.$pitchid.'?success=success');  
  }



  function createcategory($name) {
    db_q(db_insert('pitch_categories'), $name, category_active, $this->time);
    
    relocate(url.'/pitch/manage#pitchcategories');
  }

  function editcategory($categoryid, $name) {
    db_q("update pitch_categories set name='%s' where pitch_categoryid='%s'", $name, $categoryid);
    
    relocate(url.'/pitch/manage#pitchcategories');
  }
  
  function deletecategory($categoryid) {
    if(is_array($_POST['selected'])) {
      foreach($_POST['selected'] as $categoryid) {

        db_q("update pitch_categories set status='%s' where pitch_categoryid='%s'", category_inactive, $categoryid);
      }
    } else {      

      db_q("update pitch_categories set status='%s' where pitch_categoryid='%s'", category_inactive, $categoryid);    
    }
    relocate(url.'/pitch/manage#pitchcategories');
  }


  function approve($pitchid) {

    db_q("update pitches set status='%s' where pitchid='%d'", pitch_approved, $pitchid);
    
    relocate(url.'/pitch/manage');
  }

  function unapprove($pitchid) {

    db_q("update pitches set status='%s' where pitchid='%d'", pitch_rejected, $pitchid);
    
    relocate(url.'/pitch/manage');
  }

  function reject($pitchid) {
    db_q("update pitches set status='%s' where pitchid='%d'", pitch_rejected, $pitchid);
    
    relocate(url.'/pitch/manage');
  }

  function delete($pitchid) {
    db_q("update pitches set status='0' where pitchid='%d'", $pitchid);
    
    relocate(url.'/pitch/manage');
  }



  function feature_maker($active_user, $userid) {
  
	db_q("update users set featured='%d' where userid='%s'", 1, $userid) ;
	header('Location: http://www.pitchmaker.com/user/manage');
   
    reloacte(url.'/user/manage');
  }

  function unfeature_maker($active_user, $userid) {
  
	db_q("update users set featured='%d' where userid='%s'", 0, $userid) ;
	header('Location: http://www.pitchmaker.com/user/manage');

  }

  function maker_apply($active_user,$userid,$tags,$full_name,$email_address,$bio,$title,$company,$company_logo) {

	// Company Logo:
    $image = $_FILES['company_logo'];  

	$file_name = uniqid('file_', false).'.jpg';
    $image = process_cropped_organic_image($image, $imageid, 'image', root.'media/', 270, 270, false, $file_name);

    #if(is_object($image)) return $image;
    #if(!$image) return report("Please upload a cover photo.", 'coverphoto', $this->db_table); 

	if(!count($tags)) return report("Please select the categories.", 'coverphoto', $this->db_table);

	// Categories:
	$categories = array();

	foreach($tags as $categoryid){
		$pitch_categories = db_single(db_q("SELECT * FROM pitch_categories WHERE pitch_categoryid='%d'", $categoryid));
		
		$categories[$categoryid] = $pitch_categories->name;
	}

	$serialized_tags = serialize($categories);

  
	db_q("update users set full_name='%s', email_address='%s', bio='%s', title='%s', company='%s', company_logo='%s', categories='%s', permission='%d', requesting_maker='%d' where userid='%s'", $full_name,$email_address,$bio,$title,$company,$imageid, $serialized_tags, user_pitcher, maker_requesting, $userid);
	header('Location: http://www.pitchmaker.com/user/profile?success=requesting');

  }  


  function maker_approve($active_user, $userid) {
  
	db_q("update users set requesting_maker='%d', permission='%d' where userid='%s'", maker_approved, user_maker, $userid) ;
	header('Location: http://www.pitchmaker.com/user/manage');
   
    reloacte(url.'/user/manage');
  }


  function maker_reject($active_user, $userid) {
  
	db_q("update users set requesting_maker='%d' where userid='%s'", maker_rejected, $userid) ;
	header('Location: http://www.pitchmaker.com/user/manage');
   
    reloacte(url.'/user/manage');
  }

  function feature_up($active_user, $featureid, $sequence) {

	$changed_feature = db_single(db_q("SELECT * FROM features WHERE genkey='%d'", $sequence));
	
	$current_feature = db_single(db_q("SELECT * FROM features WHERE featureid='%d'", $featureid));

	db_q("update features set genkey='%d' where featureid='%s'", $sequence, $featureid);

	db_q("update features set genkey='%d' where featureid='%s'", $current_feature->genkey, $changed_feature->featureid);

	header('Location: http://www.pitchmaker.com/content/manage');

  } 

  function update_settings($active_user,$userid,$old_password,$new_password,$confirm_password,$profile_image,$tags,$full_name,$email_address,$bio,$title,$company,$company_logo) {

    /*  if($new_password) {

          if(encrypt($old_password) !== user_password($active_user)) return report('Please enter your old password correctly.', 'old_password', 'users');
          else if($new_password !== $confirm_password) {
              return report('Your new passwords did not match', 'new_password', 'users');
          } else if(strlen($new_password) < 5) {
              return report('Your new password must be at least 5 characters long.', 'new_password', 'users');
          } else if($new_password == $password_hint) {
              return report('Your password hint cannot be your password.', 'password_hint', 'users');
          } else {
              $reserve_password = $new_password;
              $password = encrypt($new_password);
              db_q("update users set password='%s' where ='%s'", $password, $password, $active_user);
              $this->authenticate(user_email_address($userid), $password, true, true);
          }
      }*/
	// Company Logo:
    $image = $_FILES['company_logo'];  
	
	if($_FILES['company_logo']['size']) {

		$file_name = uniqid('file_', false).'.jpg';
	    $image = process_cropped_organic_image($image, $imageid, 'image', root.'media/', 270, 270, false, $file_name);	
	}


	// Profile Image:
    $image2 = $_FILES['profile_image'];  
    
    if($_FILES['profile_image']['size']) {
		$file_name = uniqid('file_', false).'.jpg';
	    $image2 = process_cropped_organic_image($image2, $imageid2, 'image', root.'media/', 270, 270, false, $file_name);
	
		$file_name = uniqid('file_', false).'.jpg';
		$small_image = process_square_organic_image($image2, $smallid, 'file_thumb', root.'media/', user_image_width, false, $file_name);    
    }

	// Categories:
	$categories = array();

	foreach($tags as $categoryid){
		$pitch_categories = db_single(db_q("SELECT * FROM pitch_categories WHERE pitch_categoryid='%d'", $categoryid));
		
		$categories[$categoryid] = $pitch_categories->name;
	}

	$serialized_tags = serialize($categories);
	
	db_q("update users set full_name='%s', email_address='%s', bio='%s', title='%s', company='%s', company_logo='%s', profile_image='%s', small_profile_image='%s', categories='%s' where userid='%s'", $full_name,$email_address,$bio,$title,$company,$imageid,$imageid2,$smallid,$serialized_tags,$userid) ;
	header('Location: http://www.pitchmaker.com/user/settings?message=You have successfully updated your settings.');

  }  
 
  function color($active_user, $pitchid, $colorid) {
 
  	$user_info = db_single(db_q("SELECT * FROM users WHERE email_address='%s' AND permission <> 0", $active_user));

	db_q("UPDATE pitch_ratings SET status=0 WHERE userid='%d' AND pitchid='%d'", $user_info->userid, $pitchid);

	db_q(db_insert('pitch_ratings'), $user_info->userid, $pitchid, $colorid,1,time());
	
	if($colorid == pitch_green) db_q("UPDATE pitches SET status='%d' WHERE pitchid='%d'", pitch_greenlit, $pitchid);
 	
	header('Location: http://www.pitchmaker.com/maker/dashboard');  	
  } 

  function share_upload($active_user, $pitchid) {

	if (!empty($_FILES)) {
	     
	    $tempFile = $_FILES['file']['tmp_name'];          //3             
	      
	    $targetPath = '/var/www/vhosts/pitchmaker.com/httpdocs/dropzone/files/';  //4

		$ext = explode('.',$_FILES['file']['name']);
		$extension = $ext[1];
		
		$targetFileName = uniqid('file_', false).'.'.$extension;
		
		$targetFile =  $targetPath.$targetFileName;
	 
	    move_uploaded_file($tempFile, $targetFile); //6
	    
	    db_q(db_insert('pitch_files'), $pitchid, $active_user, $targetFileName, '', '', '', 1, time());
     
	} 
	 
  }
 
 
  function pitch_complete($active_user, $pitchid, $terms_of_service) {



	#if(!$terms_of_service) return report("You must agree to our Terms of Service to successfully submit your pitch.", 'terms_of_service', $this->db_table); 

  
	//db_q(db_insert('pitch_ratings'), $pitchid, $active_user, $targetFileName, '', '', '', 1, time());  
	
	db_q("UPDATE pitch_files SET status=2 WHERE pitchid='%d' AND status=1 ", $pitchid);
	
	db_q("UPDATE pitches SET status='%d' WHERE pitchid='%d'", pitch_submitted, $pitchid);
	
	relocate(url.'/user/profile/?success=requesting');
  } 
 
  
  function send_pitch_submission_confirmation($pitchid) {
  
  	$pitch_info = db_single(db_q("select * from pitches where pitchid='%d'", $pitchid));
  
    globalize('pitchid', $pitchid);

  	$body = load_mail_template('mail-header-system').load_mail_template('mail-submission-confirmation').load_mail_template('mail-footer-system');		

    //send mail to customer
  	$mail = new phpmailer();
  	$mail->IsHTML(true);
  
  	$mail->From     = orders_from_address;
  	$mail->FromName = orders_from_name;
  
  	$subject = 'Your '.web_name.' Pitch Submission Confirmation';
  
  	$mail->Subject  = strip_tags(to_mail_subject($subject));
  
  	$mail->Mailer   = "smtp";
  	$mail->SMTPAuth = false;
  	if(defined('smtp_port')) $mail->Port = smtp_port;
  	$mail->Username = orders_smtp_address;
  	$mail->Password = orders_smtp_password;
  	$mail->Host     = orders_smtp_host;
  	if(defined('use_qmail') and use_qmail === true) $mail->IsQmail();
  	$mail->Body      = $body;

    $mail->AddAddress($pitch_info->username);
  	$mail->Send();

    $mail->ClearAddresses();

    $mail->AddAddress("chao@charged.fm");
    $mail->Send();

  }  
  
  function count_color($pitchid) {
  	
  	$result = new StdClass;
  	
  	$count_green = db_single(db_q("SELECT count(id) AS count FROM pitch_ratings WHERE pitchid='%d' AND status<>0 AND color='%d' ", $pitchid, pitch_green));
  	$count_yellow = db_single(db_q("SELECT count(id) AS count FROM pitch_ratings WHERE pitchid='%d' AND status<>0 AND color='%d' ", $pitchid, pitch_yellow));
  	$count_red = db_single(db_q("SELECT count(id) AS count FROM pitch_ratings WHERE pitchid='%d' AND status<>0 AND color='%d' ", $pitchid, pitch_red));

  	$result->green = $count_green->count;
  	$result->yellow = $count_yellow->count;
  	$result->red = $count_red->count;
 
  	
  	return $result;
  
  }
  
  function contact_us($active_user,$full_name, $email_address, $subject, $comments) {
  	
  	if(!$subject) return report("Please select a subject.", 'subject', $this->db_table);
  	
  	if(!$comments) return report("Please write a comment.", 'comment', $this->db_table);
 
  	$subject = $subject.' Inquiry';

	$body = '
Hi,
'.$full_name.'	('.$email_address.') has sent you the following message: </br>	
'.$comments;

    //send mail to customer
  	$mail = new phpmailer();
  	$mail->IsHTML(true);
  
  	$mail->From     = 'no-reply@pitchmaker.com';
  	$mail->FromName = 'Pitch Maker';

  	$mail->Subject  = strip_tags(to_mail_subject($subject));
  
  	$mail->Mailer   = "smtp";
  	$mail->SMTPAuth = false;
  	if(defined('smtp_port')) $mail->Port = smtp_port;
  	$mail->Username = orders_smtp_address;
  	$mail->Password = orders_smtp_password;
  	$mail->Host     = orders_smtp_host;
  	if(defined('use_qmail') and use_qmail === true) $mail->IsQmail();
  	$mail->Body      = $body;

    $mail->AddAddress("david@charged.fm");
    $mail->Send();

    $mail->ClearAddresses();

    $mail->AddAddress("info@pitchmaker.com");
    $mail->Send();
  	
  	relocate(url.'/contact/us?message=The email has been sent!');
  
  }
    
  function publish($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_approved, $postid);
    log_action();
    
    relocate(url.'/blog/manage#'.$this->db_table);
  }
  
  function unpublish($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_unapproved, $postid);
    log_action();
    
    relocate(url.'/blog/manage#'.$this->db_table);
  }
  
  function publish_page($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_active, $postid);
    log_action();
    
    relocate(url.'/page/manage');
  }
  
  function unpublish_page($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_unpublished, $postid);
    log_action();
    
    relocate(url.'/page/manage');
  }
    
  function submit($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_unpublished, $postid);
    log_action();
    
    //notify editors:
    mail('tommy@charged.fm', 'New Article! '.post_title($postid), 'A new post has been submitted on Charged.fm: '.url.'/blog/post/'.$postid.'/'.slug(post_title($postid)));
    
    relocate(url.'/blog/manage#'.$this->db_table);
  }
  
  function unsubmit($postid) {
    db_q("update posts set status='%d' where postid='%d'", post_draft, $postid);
    log_action();
    
    relocate(url.'/blog/manage#'.$this->db_table);
  }  

  
  function page_images($query, $per_page = 5) {
    $images = array();
    
    while($i = db_all($query)) {
      $n++;
      $image = substr_btwn(publish($i->body), '<img src="', '"');
      $current_page = ceil($n/$per_page);
      if($image and !$images[$current_page]) {
        $images[$current_page] = (!_strpos('_page.jpg', $image)) ? str_replace('.jpg', '_page.jpg', $image) : $image ;
      }
    }
    
    for($i = 0; $i < ceil(db_num($query)/$per_page); $i++)
      if(!$images[$i]) $images[$i] = url.'/images/default-pagination.jpg';
    
    return $images;
  }
  
  function url($postid) {
    return url.'/blog/post/'.$postid.'/'.slug(post_title($postid));
  }
  
  function delete_selected($username, $selected) {
    if(is_array($selected)) {
      foreach($selected as $postid) {
        if($postid and post_username($postid) !== $username and !is_mod($username)) user_deny();
    
        $this->unprocess_tags_and_categories($username, $postid, post_tags($postid), post_categories($postid));
        db_q("update comments set status='%s' where moduleid='%s' and module='post'", comment_inactive, $postid);
        db_q("update posts set status='%s' where postid='%s'", post_inactive, $postid);
      }
    }
    
    relocate(url.'/blog/manage#'.$this->db_table);
  }
  
  function delete_tag($username, $tagid) {
    $name = tag_tag($tagid);
    $list_tags = db_q("select * from posts where tags like '%%s%'", $name);
    while($t = db_all($list_tags)) {
      $tags = $this->process_tags($username, $t->postid, str_replace($name, '', $t->tags));
      db_q("update posts set tags='%s' where postid='%d'", $tags, $t->postid);
    }
    
    if(is_array($_POST['selected'])) {
      foreach($_POST['selected'] as $tagid) {
        db_q("update tags set status='%s' where tagid='%s'", post_tag_inactive, $tagid);
      }
    } else db_q("update tags set status='%s' where tagid='%s'", post_tag_inactive, $tagid);
    
    relocate(url.'/blog/manage#tags');
  }
  
  function unprocess_tags_and_categories($username, $postid, $tags, $categories) {
    $categories = explode(',', $categories);
    $tags = explode(',', $tags);
    
    if(is_array($categories) and count($categories) > 0) {
      foreach($categories as $category) {
        $category = trim(ucwords($category));
        $categoryid = $this->categoryid($username, $category);
        if($this->is_post_category($username, $postid, $category) and $this->number_category($username, $categoryid) == 1) db_q("update categories set status='%s' where category='%s' and username='%s'", post_category_inactive, $category, $username);
        else if($this->is_category($username, $category)) db_q("update categories set status='%s' where category='%s' and username='%s'", post_category_inactive, $category, $username);
      }
    }
    
    if(is_array($tags) and count($tags) > 0) {
      foreach($tags as $tag) {
        $tag = trim(ucwords($tag));
        $tagid = $this->tagid($username, $tag);
        if($this->is_post_tag($username, $postid, $tag) and $this->number_tag($username, $tag) == 1) db_q("update tags set status='%s' where tag='%s' and username='%s'", post_tag_inactive, $tag, $username);
        else if($this->is_tag($username, $tag)) db_q("update tags set status='%s' where tag='%s' and username='%s'", post_tag_inactive, $tag, $username);
      }
    }
  }
  
  function has_posts($username) {
    $p = db_single(db_q("select postid from posts where username='%s' and status='%s' limit 1", $username, post_active));
    return $p->postid;
  }
  
  function number() {
    return (int) db_num(db_q("select postid from posts where status='%s'", post_active));
  }
  
  function has_post_by_day($username, $day) {
    $start = $day;
    $end = $day + one_day() + 1;
    
    $p = db_single(db_q("select postid from posts where time between %s and %s and status='%s' limit 1", $start, $end, post_active));
    return $p->postid ? url.'/blog/byday/'.$day : 0 ;
  }
  
  function posts_by_day($username, $day) {
    $start = $day;
    $end = $day + one_day();
    
    return db_num(db_q("select postid from posts where time between %s and %s and status='%s'", $start, $end, post_active));
  }
  
  function posts_by_year($username, $year) {
    $start = first_day_in_month(1, $year);
    $end = last_day_in_month(12, $year);
    
    return db_num(db_q("select postid from posts where time between %s and %s and username='%s' and status='%s'", $start, $end, $username, post_active));
  }
  
  function posts_by_month_and_year($username, $month, $year) {
    $start = first_day_in_month($month, $year);
    $end = last_day_in_month($month, $year);
    
    return db_num(db_q("select postid from posts where time between %s and %s and username='%s' and status='%s'", $start, $end, $username, post_active));
  }
  
  function postid($username, $time) {
    $p = db_single(db_q("select postid from posts where username='%s' and time='%s' limit 1", $username, $time));
    return $p->postid;
  }
  
  function create_category($username, $category_name, $parent_category, $description) {
    if(!$parent_category and $this->is_category($username, $category_name)) return report("You've already created this parent category and cannot make duplicate categories.");
    else if($parent_category and $this->is_child_category($username, $parent_category, $category_name)) return report("You've already created this category  under ".category_category_name($parent_category)." and cannot make duplicate categories.");
  
    db_q(db_insert('categories'), $username, $category_name, $parent_category, $description, post_category_active, $this->time);
    
    relocate(url.'/manager/categories');
  }
  
  function edit_category($username, $categoryid, $category_name, $parent_category, $description) {
    if($categoryid and post_category_username($categoryid) !== $username) user_deny();
    
    db_q(db_insert('categories'), $username, $category_name, $parent_category, $description, post_category_active, $this->time);
    
    relocate(url.'/manager/categories');
  }
  
  function delete_category($username, $categoryid) {
    $list_posts = db_q("select postid from posts where username='%s' and categories like '%\"%s\"%' or categories and status='%s'", $username, $categoryid, post_active);
    while($p = db_all($list_posts)) {
      $categories = post_categories($p->postid);
      $categories = array_remove($categoryid, $categories);
      $categories = (empty($categories)) ? array('Uncategorize') : serialize($categories) ;
      db_q("update posts set categories='%s' where postid='%s'", $categories, $p->postid);
    }
    
    db_q("update categories set status='%d' where username='%s' and categoryid='%d'", post_category_inactive, $username, $categoryid);
    relocate(url.'/blog/manage#blogcategories');
  }
  
  function delete_selected_categories($username, $selected) {
    if(is_array($selected)) {
      foreach($selected as $categoryid) {
        
        $list_posts = db_q("select postid from posts where username='%s' and categories like '%%s%' or categories and status='%s'", $username, '"'.$categoryid.'"', post_active);
        while($p = db_all($list_posts)) {
          $categories = post_categories($p->postid);
          $categories = array_remove($category, $categories);
          $categories = (empty($categories)) ? array('Uncategorize') : serialize($categories) ;
          db_q("update posts set categories='%s' where postid='%s'", $categories, $p->postid);
        }
    
        db_q("update categories set status='%d' where username='%s' and categoryid='%d'", post_category_inactive, $username, $categoryid);
      }
    }
    relocate(url.'/manager/categories');
  }
  
  function children_by_category($username, $category) {
    $number = 0;
    
    $list_children = db_q("select * from categories where username='%s' and parent_category='%s' and status='%s'", $username, $category, post_category_active);
    while($c = db_all($list_children)) {
      $number += $this->number_category($username, $c->categoryid);
    }
    
    return $number;
  }
  
  function has_categories($username) {
    $c = db_single(db_q("select categoryid from categories where username='%s' and parent_category<>'0' and status='%s' limit 1", $username, post_category_active));
    return $c->categoryid ? $c->categoryid : 0 ;
  }
  
  function is_category($username, $category) {
    $c = db_single(db_q("select categoryid from categories where username='%s' and parent_category='0' and category_name='%s' and status='%s' limit 1", $username, $category, post_category_active));
    return $c->categoryid ? $c->categoryid : 0 ;
  }
  
  function is_child_category($username, $parent_category, $category) {
    $c = db_single(db_q("select categoryid from categories where username='%s' and parent_category='%s' and category_name='%s' and status='%s' limit 1", $username, $parent_category, $category, post_category_active));
    return $c->categoryid ? $c->categoryid : 0 ;
  }
  
  function is_post_category($username, $postid, $category) {
    $p = db_single(db_q("select postid from posts where username='%s' and postid='%s' and categories like '%\"%s\"%' and status='%s' limit 1", $username, $postid, $category, post_category_active));
    return $p->postid ? $p->postid : 0 ;
  }
  
  function categoryid($categoryid) {
    $c = db_single(db_q("select name from blog_categories where status='%s' and blog_categoryid='%d' limit 1", category_active, $categoryid));
    return $c->name ? $c->name : 0 ;
  }
  
  function number_category($username, $category) {
    return db_num(db_q("select postid from posts where username='%s' and categories like '%\"%s\"%' and status='%s'", $username, $category, post_category_active));
  }
  
  function process_tags($username, $postid, $tags) {
    $tags = array_unique(explode(',', $tags));
    $processed_tags = array();
  
    if(is_array($tags) and count($tags) > 0) {
      foreach($tags as $tag) {
        $tag = trim(ucwords($tag));
        $processed_tags []= $tag;
        if($tag) {
          if($postid) {
            if(!$this->is_post_tag($username, $postid, $tag) and !$this->is_tag($username, $tag)) db_q(db_insert('tags'), $username, $tag, post_tag_active, $this->time);
          } else {
            if(!$this->is_tag($username, $tag)) db_q(db_insert('tags'), $username, $tag, post_tag_active, $this->time);
          }
        }
      }
    }
    
    return implode(', ', $processed_tags);
  }
  
  function has_tags($username) {
    $t = db_single(db_q("select tagid from tags where username='%s' and status='%s' limit 1", $username, post_tag_active));
    return $t->tagid ? $t->tagid : 0 ;
  }
  
  function is_tag($username, $tag) {
    $t = db_single(db_q("select tagid from tags where username='%s' and tag='%s' and status='%s' limit 1", $username, $tag, post_tag_active));
    return $t->tagid ? $t->tagid : 0 ;
  }
  
  function is_post_tag($username, $postid, $tag) {
    $p = db_single(db_q("select postid from posts where username='%s' and postid='%s' and tags like '%%s%' and status='%s' limit 1", $username, $postid, $tag, post_active));
    return $p->postid ? $p->postid : 0 ;
  }
  
  function tagid($username, $tag) {
    $t = db_single(db_q("select tagid from tags where tag='%s' and status='%s' limit 1", $tag, post_tag_active));
    return $t->tagid ? $t->tagid : 0 ;
  }
  
  function number_tag($username, $tag) {
    return db_num(db_q("select postid from posts where tags like '%%s%' and status='%s'", $tag, post_active));
  }
  
  function number_all_tag($status) {
    return db_num(db_q("select tagid from tags where status='%s'", $status));
  }
  
  function related_performer($performerid, $postid, $numbers=2) {
  	$output = array();
    $list_posts = db_q("select postid from posts where (performerid_1='%d' or performerid_2='%d' or performerid_3='%d' or performerid_4='%d' or performerid_5='%d') and postid<>'%d' and status='%s' order by time desc limit 2", $performerid, $performerid, $performerid, $performerid, $performerid, $postid, post_active);    
    while($p = db_all($list_posts)) {
				array_push($output, $p->postid);
    }
    return $output;
  }
  
}
?>
