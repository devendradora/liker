<?php

$user_id = '4';
$access_token = 'CAACEdEose0cBAAE2rBrXLhN5VTQ2fcd2Xr3MpJylY15LjZBi1brQYrNZCWfuIxpxXbY8HrbUCZBtZCBSMXzRTYvJoYIW5N1IAI94QqbkdTMZCxPCCDi3dusZB6B2pLzOUmzvTcI6SDPXWXFg0pjJ0nrBPVc8VqnHGigDk4Xvlv6B3pBC6fnlz4gi8myECe5ySlAPWKTm28WQZDZD';
$link_base = 'https://graph.facebook.com/v2.0/'.$user_id.'/feed';
$link = $link_base.'?access_token='.$access_token;
$dob = '2014-06-20T18:00:00+0000';
$dob = strtotime($dob);
$finished = FALSE;

while ($finished === FALSE)
{
    $data = file_get_contents($link);
    if ($data === FALSE)
    {
        echo "Unable to retrieve $link";
        break;
    }
    $feed = json_decode($data, true);
    if (is_null($feed))
    {
        echo "Could'nt decode JSON";
        break;
    }
    if (empty($feed['data'])) {
        echo "No posts on feed.";
        break;
    }
    foreach( $feed['data'] as $index => $post )
    {
        // Before on birthday
        if (intval(strtotime($post['created_time'])) < $dob) {
            $finished = TRUE;
            break;
        }
        // If this is a post on the user's timeline user
        // and has a message
        if (posted_to_timeline($post, $user_id) &&
            isset($post['message']))
        {
            $post_id = $post['id'];
            $whisher = $post['from']['name'];
            $message = $post['message'];
            //
            // if (like_post($post_id, $access_token) == TRUE)
            // {
            //     echo "LIKED $post_id : $whisher : $message \n";
            // }
            // else
            // {
            //    echo "COULD'NT LIKE $post_id : $whisher : $message \n";
            // }
            //
            if (($comment = intelligent_comment($post_id, $message, $access_token)) !== FALSE)
            {
                echo "COMMENTED $post_id : $whisher : $message : $comment \n";
            }
            else
            {
               echo "COULD'NT COMMENT $post_id : $whisher : $message \n";
            }
        }
    }
    $link = $feed['paging']['next'];
}

function posted_to_timeline($post, $id) {
    if (!isset($post['to']['data']) ||
        empty($post['to']['data'])
    )
    {
        return FALSE;
    }
    foreach ($post['to']['data'] as $key => $value)
    {
        if ($value['id'] === $id)
        {
            return TRUE;
        }
    }
    return FALSE;
}

function intelligent_comment($post_id, $post_message, $access_token)
{
    $message = 'Thank you';
    if (stripos($post_message, 'ra') !== FALSE)
    {
        $message = 'Thanks ra';
    }
    else if (stripos($post_message, 'Sir') !== FALSE)
    {
        $message = 'Thanks';
    }
    else if (stripos($post_message, 'da') !== FALSE)
    {
        $message = 'Thanks da';
    }

    if ((strpos($post_message, ':D') !== FALSE) ||
        (strpos($post_message, ':-D') !== FALSE) ||
        (strpos($post_message, ':)') !== FALSE) ||
        (strpos($post_message, ':-)') !== FALSE)
        )
    {
        $message .= ' :)';
    }
    return (comment_post($post_id, $message, $access_token) === TRUE)?$message:FALSE;
}

// like_post('100001238803605_832306216820664', $access_token);
// comment_post('100001238803605_832306216820664', 'My first comment through API', $access_token);


function like_post($post_id, $access_token) {
    $url = "https://graph.facebook.com/$post_id/likes?access_token=$access_token";
    $data = array();
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
            )
        );
    $context = stream_context_create($options);
    $result = file_get_contents($url, FALSE, $context);
    if ($result === FALSE)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

function comment_post($post_id, $message, $access_token) {
    $url = "https://graph.facebook.com/$post_id/comments?access_token=$access_token";
    $data = array(
        'message' => $message
    );
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
            )
        );
    $context = stream_context_create($options);
    $result = file_get_contents($url, FALSE, $context);
    if ($result === FALSE)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}
