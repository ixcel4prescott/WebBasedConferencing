<?
class SearchController extends AppController{
  var $uses        = Array('Account', 'Contact', 'Room', 'RoomView');
  var $permissions = GROUP_ALL;

  function index(){
    $user = $this->Session->read('User');

    $criteria = Array();
    if(!is_null($user['Resellers']))
      $criteria['RoomView.resellerid'] = $user['Resellers'];

    $query = @$_GET['query'];

    if($query) {
      $room_criteria = $criteria;
      $room_criteria['OR'] = Array( 'RoomView.cec' => $query,
                                    'RoomView.pec' => $query );
      $room_criteria [] = "RoomView.bridgeid = DefaultBridge.bridge_id";
      if($account = $this->Account->get($query, $user))
        $this->redirect('/accounts/view/' . $account['Account']['acctgrpid']);
      elseif($room = $this->Room->get($query, $user))
        $this->redirect('/rooms/view/' . $room['Room']['accountid']);
      elseif($room = $this->RoomView->find($room_criteria))
        $this->redirect('/rooms/view/' . $room['RoomView']['accountid']);
    }
    $this->set('query', $query);
  }
}