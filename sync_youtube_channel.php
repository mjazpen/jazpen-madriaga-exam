<?php 

require_once 'database.php';

function save_channel($data){
    
    // clean the data
    array_map('htmlentities', $data);

    try{
        $db = db_object();

        $sql = "INSERT INTO youtube_channels (channel_id, name, description, profile_picture) VALUES (:channel_id, :name, :description, :profile_picture)";
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }

}

function load_channel($channel_id, $new_channel = false){

    global $api_key;
    // ONLY FETCH THE CHANNEL IF IT DOES NOT EXIST IN THE DATABASE
    if(!channel_exists($channel_id) || $new_channel)
    {
        // Fetch channel information
        $channelUrl = "https://www.googleapis.com/youtube/v3/channels?key=$api_key&id=$channel_id&part=snippet";
        $channelResponse = file_get_contents($channelUrl);
        $channelData = json_decode($channelResponse, true);
        $channelInfo = $channelData['items'][0]['snippet'];

        $res= save_channel([
            'channel_id' => $channel_id,
            'name' => $channelInfo['title'],
            'description' => $channelInfo['description'],
            'profile_picture' => $channelInfo['thumbnails']['default']['url']
        ]);

        if(!$res){
            return false;
        }
    }
    
    // fetch the channel from the database
    return get_channel_by_id($channel_id);
}

function get_channel_by_id($channel_id){
    try{

        $db = db_object();
        $sql = "SELECT * FROM youtube_channels WHERE channel_id = :channel_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':channel_id', $channel_id);
        $stmt->execute();
        $channel = $stmt->fetch(PDO::FETCH_OBJ);
    }
    catch(PDOException $e){
        echo $e->getMessage();
    }
    return $channel;
}

function save_youtube_channel_videos($data, $channel_id){
    // clean the data
    array_map('htmlentities', $data);
    
    try{
        $db = db_object();
        
        $sql1 = "INSERT INTO youtube_channel_videos (link, title, description, thumbnail, channel_id) VALUES (:link, :title, :description, :thumbnail, :channel_id)";
        $stmt1 = $db->prepare($sql1);
        $data['channel_id'] = $channel_id;
        return $stmt1->execute($data);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

function load_channel_videos($channel_id,$page = 1,$new_videos_only = false){
    global $api_key;

    if($new_videos_only){
        fetch_new_videos($channel_id);
    }
    
    $data  = get_channel_videos_by_id($channel_id,$page);
    
    if(!$data){
        fetch_new_videos($channel_id);
        $data  = get_channel_videos_by_id($channel_id,$page);
    }


    return [
        'data' => $data,
        'current_page' => $page,
        'pagination' => generate_pagination(count(get_channel_videos_by_id($channel_id,$page,20, true)), $page)
    ];
    
    
}

function fetch_new_videos($channel_id){
    global $api_key;
    
    // Fetch 100 latest videos
    $videosUrl = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$channel_id&part=snippet&type=video&order=date&maxResults=50";
    $videosResponse = file_get_contents($videosUrl);
    $videosData = json_decode($videosResponse, true);

    // request the next 50 videos 
    $videosUrl = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$channel_id&part=snippet&type=video&order=date&maxResults=50&pageToken=".$videosData['nextPageToken'];
    $videosResponse = file_get_contents($videosUrl);
    $videosData2 = json_decode($videosResponse, true);

    // merge the two requests
    $videosData['items'] = array_merge($videosData['items'], $videosData2['items']);

    $formatted_data = [];
    foreach ($videosData['items'] as $video) {
        $videoId = $video['id']['videoId'];
        $videoTitle = $video['snippet']['title'];
        $videoDescription = $video['snippet']['description'];
        $videoLink = "https://www.youtube.com/watch?v=$videoId";
        $videoThumbnail = $video['snippet']['thumbnails']['default']['url'];
        $formatted_data[] = [
            'link' => $videoLink,
            'title' => $videoTitle,
            'description' => $videoDescription,
            'thumbnail' => $videoThumbnail
        ];
    }

    try{
        foreach ($videosData['items'] as $video) {
            $videoId = $video['id']['videoId'];
            $videoTitle = $video['snippet']['title'];
            $videoDescription = $video['snippet']['description'];
            $videoLink = "https://www.youtube.com/watch?v=$videoId";
            $videoThumbnail = $video['snippet']['thumbnails']['default']['url'];
            
            $res = save_youtube_channel_videos([
                'link' => $videoLink,
                'title' => $videoTitle,
                'description' => $videoDescription,
                'thumbnail' => $videoThumbnail
            ], $channel_id);

            if(!$res){
                return false;
            }
            
        }
    }
    catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $formatted_data;
}

function get_channel_videos_by_id($channel_id,$page = 1, $limit = 20, $all=false){
    try{
        $offset = ($page - 1) * $limit;
        
        $db = db_object();
        if($all){
            $sql = "SELECT * FROM youtube_channel_videos WHERE channel_id = :channel_id";
            $stmt = $db->prepare($sql);
        }
        else{
            $sql = "SELECT * FROM youtube_channel_videos WHERE channel_id = :channel_id LIMIT :offset, :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':channel_id', $channel_id, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
    }
    catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $results;
}

function generate_pagination($total, $current_page, $limit = 20){
    $pages = ceil($total / $limit);
    $pagination = [];
    for($i = 1; $i <= $pages; $i++){
        $pagination[] = [
            'page' => $i,
            'active' => $i == $current_page ? true : false
        ];
    }
    return $pagination;
}

function get_all_channels(){
    try{
        $db = db_object();
        $sql = "SELECT * FROM youtube_channels";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $results;
}

function channel_exists($channel_id){
    return get_channel_by_id($channel_id) ? true : false;
}