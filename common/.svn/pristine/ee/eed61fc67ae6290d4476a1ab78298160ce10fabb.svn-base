<?

class reportBase extends AppModel
{
  public function findAll($conditions = null, $fields = null, $order = null, $limit = null, $page = 1, $recursive = null){
    $sql = "SELECT SESSIONPROPERTY ('ANSI_NULLS') As [ANSI_NULLS],
                   SESSIONPROPERTY ('QUOTED_IDENTIFIER') As [QUOTED_IDENTIFIER],
                   SESSIONPROPERTY ('ANSI_WARNINGS') As [ANSI_WARNINGS],
                   SESSIONPROPERTY ('ANSI_PADDING') As [ANSI_PADDING],
                   SESSIONPROPERTY ('CONCAT_NULL_YIELDS_NULL') As [CONCAT_NULL_YIELDS_NULL]";
    $originalSessionProperty = $this->query($sql);
    /* Debug Dump
    echo '<br>original<br>';
    print_r($originalSessionProperty);   */
    
    $sql = "SET ANSI_NULLS ON";
    $this->query($sql);
    $sql = "SET QUOTED_IDENTIFIER ON";
    $this->query($sql);
    $sql = "SET ANSI_WARNINGS ON";
    $this->query($sql);
    $sql = "SET ANSI_PADDING ON";
    $this->query($sql);
    $sql = "SET CONCAT_NULL_YIELDS_NULL ON";
    $this->query($sql);

    /* Debug Dump
    $sql = "SELECT SESSIONPROPERTY ('ANSI_NULLS') As [ANSI_NULLS],
                   SESSIONPROPERTY ('QUOTED_IDENTIFIER') As [QUOTED_IDENTIFIER],
                   SESSIONPROPERTY ('ANSI_WARNINGS') As [ANSI_WARNINGS],
                   SESSIONPROPERTY ('ANSI_PADDING') As [ANSI_PADDING],
                   SESSIONPROPERTY ('CONCAT_NULL_YIELDS_NULL') As [CONCAT_NULL_YIELDS_NULL]";
    $ModifiedSessionProperty = $this->query($sql);
    echo '<br>modified<br>';
    print_r($ModifiedSessionProperty);*/
    
    $results = parent::findAll($conditions, $fields, $order, $limit, $page, $recursive );
           //print_r($results);
    $sql = "SET ANSI_NULLS ".(($originalSessionProperty[0][0]['ANSI_NULLS'] == 1)? 'ON' : 'OFF');
    $this->query($sql);
    $sql = "SET QUOTED_IDENTIFIER ".(($originalSessionProperty[0][0]['QUOTED_IDENTIFIER'] == 1)? 'ON' : 'OFF');
    $this->query($sql);
    $sql = "SET ANSI_WARNINGS ".(($originalSessionProperty[0][0]['ANSI_WARNINGS'] == 1)? 'ON' : 'OFF');
    $this->query($sql);
    $sql = "SET ANSI_PADDING ".(($originalSessionProperty[0][0]['ANSI_PADDING'] == 1)? 'ON' : 'OFF');
    $this->query($sql);
    $sql = "SET CONCAT_NULL_YIELDS_NULL ".(($originalSessionProperty[0][0]['CONCAT_NULL_YIELDS_NULL'] == 1)? 'ON' : 'OFF');
    $this->query($sql);
    //$sql = "SET DATEFORMAT ".$originalSessionProperty[0][0]['DATEFORMAT'];
    //$this->query($sql);

    /* Debug Dump
    $sql = "SELECT SESSIONPROPERTY ('ANSI_NULLS') As [ANSI_NULLS],
                   SESSIONPROPERTY ('QUOTED_IDENTIFIER') As [QUOTED_IDENTIFIER],
                   SESSIONPROPERTY ('ANSI_WARNINGS') As [ANSI_WARNINGS],
                   SESSIONPROPERTY ('ANSI_PADDING') As [ANSI_PADDING],
                   SESSIONPROPERTY ('CONCAT_NULL_YIELDS_NULL') As [CONCAT_NULL_YIELDS_NULL]";
    $RestoredSessionProperty = $this->query($sql);
    echo '<br>restored<br>';
    print_r($RestoredSessionProperty);*/
    
    return $results;
  }
  
  function openSQLSession($sql)
{
  if (empty($sql))
    return null;
  else{
    $preCheckSQL = "SELECT SESSIONPROPERTY ('ANSI_NULLS') As [ANSI_NULLS],
                   SESSIONPROPERTY ('QUOTED_IDENTIFIER') As [QUOTED_IDENTIFIER],
                   SESSIONPROPERTY ('ANSI_WARNINGS') As [ANSI_WARNINGS],
                   SESSIONPROPERTY ('ANSI_PADDING') As [ANSI_PADDING],
                   SESSIONPROPERTY ('CONCAT_NULL_YIELDS_NULL') As [CONCAT_NULL_YIELDS_NULL]";
    $originalSessionProperty = $this->query($preCheckSQL);

    $setSQL = "SET ANSI_NULLS ON";
    $this->query($setSQL);
    $setSQL = "SET QUOTED_IDENTIFIER ON";
    $this->query($setSQL);
    $setSQL = "SET ANSI_WARNINGS ON";
    $this->query($setSQL);
    $setSQL = "SET ANSI_PADDING ON";
    $this->query($setSQL);
    $setSQL = "SET CONCAT_NULL_YIELDS_NULL ON";
    $this->query($setSQL);

    $results = $this->query($sql);

    $postSQL = "SET ANSI_NULLS ".(($originalSessionProperty[0][0]['ANSI_NULLS'] == 1)? 'ON' : 'OFF');
    $this->query($postSQL);
    $postSQL = "SET QUOTED_IDENTIFIER ".(($originalSessionProperty[0][0]['QUOTED_IDENTIFIER'] == 1)? 'ON' : 'OFF');
    $this->query($postSQL);
    $postSQL = "SET ANSI_WARNINGS ".(($originalSessionProperty[0][0]['ANSI_WARNINGS'] == 1)? 'ON' : 'OFF');
    $this->query($postSQL);
    $postSQL = "SET ANSI_PADDING ".(($originalSessionProperty[0][0]['ANSI_PADDING'] == 1)? 'ON' : 'OFF');
    $this->query($postSQL);
    $postSQL = "SET CONCAT_NULL_YIELDS_NULL ".(($originalSessionProperty[0][0]['CONCAT_NULL_YIELDS_NULL'] == 1)? 'ON' : 'OFF');
    $this->query($postSQL);

    return $results;
  }
}

}