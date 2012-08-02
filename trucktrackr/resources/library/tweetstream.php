<?php
set_time_limit(60*60);
require_once(realpath(dirname(__FILE__))."/Phirehose.php");
/**
 * Example of using Phirehose to display a live filtered stream using track words
 */
class FilterTrackConsumer extends Phirehose
{
  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they should be being
     *       enqueued and processed asyncronously from the collection process.
     */
    echo "STATUS ENQUEUED<br>";
    $data = json_decode($status, true);
    if(!empty($data))
    {
        print_r($data);
        echo "DATA Printed.<br>";
    }
  }
}
echo "Class declared.<br>";
$trucks = array();
$trucks[0] = 20947718; //kintendo's userID NUMBER, not name, number.
$trucks[1] = 91076860; //seung-hyo choi's twitter userID integer
echo "Filters created.<br>";
$sc = new FilterTrackConsumer('kintendo', 'mrbing822', Phirehose::METHOD_FILTER);
echo "Consumer initiated.<br>";
$sc->setFollow($trucks);
echo "Filters set.<br>";
$sc->consume();
?>