<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/uploads/');

if(isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
{
    $response['status'] = 'false';
    if($_REQUEST['user_id'] == 1)
    {
        $collection = new MongoCollection($app_data, 'users');
        $users = $collection->find();

        if($users->count() > 0) { $response['status'] = 'true';
            foreach($users as $user)
            {
                unset($user['_id']);
                $user['company_name'] = '';
                if( !empty( $user['company_id'] ) &&  $user['company_id'] != 0 )
                {
                    $collection_com = new MongoCollection($app_data, 'company');
                    $coms = $collection_com->findOne(array('company_ID'=>(int)$user['company_id']));
                    if(isset($coms['company_ID']))
                    {
                        $user['company_name'] = $coms['company_name'];
                        $user['company_ref_id'] = $coms['company_ref'];
                    }
                }

                if($user['user_id'] == $_REQUEST['user_id'])
                {
                    $response['current_user'][] = $user;
                }
                if($user['user_id'] != $_REQUEST['user_id'])
                {

                    $start_date = new DateTime( date('d-m-Y H:i',strtotime( $user['registered_time'] )) );
                    $since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));

                    /*echo date('d F Y, H:i') . '---' . $user['registered_time'] . '-----';
                    echo $since_start->days.' days total -- ';
                    echo $since_start->y.' years -- ';
                    echo $since_start->m.' months -- ';
                    echo $since_start->d.' days -- ';
                    echo $since_start->h.' hours -- ';
                    echo $since_start->i.' minutes -- ';
                    echo $since_start->s.' seconds---';
                    echo '<br>';*/

                    if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
                    {
                        $user['duration'] = 'NOW';
                        //echo 'Now<br><br>';
                    }
                    else if($since_start->d == 0 && $since_start->h >= 0)
                    {
                        $user['duration'] = date('H:i', strtotime( $user['registered_time']));
                        //echo 'Before 1 day<br><br>';
                    }
                    else if($since_start->days == 1)
                    {
                        $user['duration'] = 'Yesterday';
                        //echo 'Yesterday<br><br>';
                    }
                    else if($since_start->days >= 2)
                    {
                        $user['duration'] = date('d/m', strtotime( $user['registered_time']));
                        //echo  date('d/m', strtotime( $user['registered_time'])) . ' <br><br>';
                    }
                    else { $user['duration'] = '--/--'; }
                    $response['data'][] = $user;
                }
            }
        }
    }
    else
    {
        $users = $app_data->company;
        $cursor = $users->find();
        if($cursor->count() > 0)
        {
            foreach($cursor as $com)
            {
                $ids = json_decode($com['user_id']);
                if(in_array($_REQUEST['user_id'], $ids))
                {
                    for($i=0;$i<=count($ids);$i++)
                    {
                        $users_l_1 = $app_data->users;
                        $cursor_u = $users_l_1->find(array('user_id'=>(int)$ids[$i]));
                        if($cursor_u->count())
                        {
                            $response['status'] = 'true';
                            foreach($cursor_u as $uu)
                            {
                                $uu['company_name'] = '';
                                if( !empty( $uu['company_id'] ) &&  $uu['company_id'] != 0 )
                                {
                                    $collection_com = new MongoCollection($app_data, 'company');
                                    $coms = $collection_com->findOne(array('company_ID'=>(int)$uu['company_id']));
                                    if(isset($coms['company_ID']))
                                    {
                                        $uu['company_name'] = $coms['company_name'];
                                        $uu['company_ref_id'] = $coms['company_ref'];
                                    }
                                }
                                unset($user['_id']);
                                if($uu['user_id'] == $_REQUEST['user_id'])
                                {
                                    $response['current_user'][] = $uu;
                                }
                                if($uu['user_id'] != $_REQUEST['user_id'])
                                {
                                    if(in_array($uu['role'],array(4,5,6,7)))
                                    {
                                        $start_date = new DateTime( date('d-m-Y H:i',strtotime( $uu['registered_time'] )) );
                                        $since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));

                                        /*echo date('d F Y, H:i') . '---' . $uu['registered_time'] . '-----';
                                        echo $since_start->days.' days total -- ';
                                        echo $since_start->y.' years -- ';
                                        echo $since_start->m.' months -- ';
                                        echo $since_start->d.' days -- ';
                                        echo $since_start->h.' hours -- ';
                                        echo $since_start->i.' minutes -- ';
                                        echo $since_start->s.' seconds---';
                                        echo '<br>';*/

                                        if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
                                        {
                                            $uu['duration'] = 'NOW';
                                            //echo 'Now<br><br>';
                                        }
                                        else if($since_start->d == 0 && $since_start->h >= 0)
                                        {
                                            $uu['duration'] = date('H:i', strtotime( $uu['registered_time']));
                                            //echo 'Before 1 day<br><br>';
                                        }
                                        else if($since_start->days == 1)
                                        {
                                            $uu['duration'] = 'Yesterday';
                                            //echo 'Yesterday<br><br>';
                                        }
                                        else if($since_start->days >= 2)
                                        {
                                            $uu['duration'] = date('d/m', strtotime( $uu['registered_time']));
                                            //echo  date('d/m', strtotime( $uu['registered_time'])) . ' <br><br>';
                                        }
                                        else { $uu['duration'] = '--/--'; }
                                        $response['data'][] = $uu;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);