<? 

class BackendController extends AppController
{
   var $uses       = Array('Backend');
   var $helpers    = Array('Pagination');
   var $components = Array('Pagination');

   var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;

   function index()
   {
     $criteria = null;

     if(!empty($_GET)) {
       $criteria = a();

       if(!empty($_GET['created'])) {
	 $criteria['DAY(Backend.created)']   = date('d', strtotime($_GET['created']));
	 $criteria['MONTH(Backend.created)'] = date('m', strtotime($_GET['created']));
	 $criteria['YEAR(Backend.created)']  = date('Y', strtotime($_GET['created']));
       }

       if(!empty($_GET['message']))
	 $criteria['Backend.message'] = 'LIKE %' . implode('%', preg_split('/\s+/', $_GET['message'], -1, PREG_SPLIT_NO_EMPTY)) . '%';
     }
     
     list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'id', 'direction', 'DESC'));
     $this->set('data', $this->Backend->findAll($criteria, NULL, $order, $limit, $page));
   }
}
