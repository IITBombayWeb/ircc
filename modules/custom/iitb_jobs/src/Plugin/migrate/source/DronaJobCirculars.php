<?php

namespace Drupal\iitb_jobs\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase; 
use Drupal\migrate\Row; 

/**
 * Provides a 'DronaJobCirculars' migrate source.
 *
 * @MigrateSource(
 *  id = "drona_job_circulars"
 * )
 */
class DronaJobCirculars extends SourcePluginBase {

  /** 
   * Initializes the iterator with the source data. 
   * @return \Iterator 
   *   An iterator containing the data for this source. 
   */ 
  protected function initializeIterator() { 
 
    // loop through the source files and find anything with a .json extension 
    // $path = dirname(DRUPAL_ROOT) . "/source-data/*.json"; 
    // $filenames = glob($path); 
    //$con = \Drupal::database();
    \Drupal\Core\Database\Database::setActiveConnection('drona');
    $con = \Drupal\Core\Database\Database::getConnection('drona');
    
    $query = $con->select('RecruitmentDetails', 'rd');
    
    //$query->leftJoin('RecruitmentProjectDetails', 'rpd', 'rd.RecruitmentSrNo = rpd.RecruitmentSrNo');
    $query->fields('rd',array('RecruitmentSrNo','RecruitmentType','PICode','DeptCode','SubmittedDate','EndDate','ApprovalDate','Status','AdvCirNo','TA','AdDateChosen','URL'));
    //$query->fields('rpd',array('ProjectSrNo','ProCode','Title','AdvJobCode','ProjectDescription'));
    
    $query->condition('rd.RecruitmentSrNo', "2015052", "=");
    //$query->range(0, 10);
    
    $result = $query->execute()->fetchAll();

    $rows = []; 
    foreach ($result as $result1) { 
        // using second argument of TRUE here because migrate needs the data to be 
        // associative arrays and not stdClass objects. 
        //$row = json_decode(file_get_contents($filename), true); // sets the title, body, etc. 
        //$row['json_filename'] = $filename;

      //Check if this circular has already been created manually or by Job designation migration. If it is then find and assign the nid to the current row so that only remaining fields are updated.
      $queryCheckExistingCircular = \Drupal::entityQuery('node')
      ->condition('type', 'job_circulars')
      ->condition('title', $result1->RecruitmentSrNo, '=');
      $nidsExistingCircular = $queryCheckExistingCircular->execute();
      if($nidsExistingCircular) {
        //If nid found then assign it.
        //print_r(reset($nids));
        $row['nid']=reset($nidsExistingCircular);
      } else {
        //If nid is not found then set value to 0 so that new circular is created.
        $row['nid']=0;
      }
      
        
      $row['title']=$result1->RecruitmentSrNo;
      $row['body']='Description';

      $row['RecruitmentSrNo']=$result1->RecruitmentSrNo;
      $row['RecruitmentType']=$result1->RecruitmentType;
      $row['PICode']=$result1->PICode;
      $row['DeptCode']=$result1->DeptCode;

      //$condept = \Drupal\Core\Database\Database::getConnection('drona');
      $querydept = $con->select('Dept', 'd');
      $querydept->condition('d.Dept_Code', $result1->DeptCode, "=");
      $querydept->fields('d',array('Name'));
      $resultdept = $querydept->execute()->fetchAll();
      //echo '<pre>';
      //print_r($resultdept);

      if(isset($resultdept[0]->Name)) {
        $row['DeptCodeRef']=$resultdept[0]->Name;
      } else {
        $row['DeptCodeRef']='';
      }

      $row['SubmittedDate']=$result1->SubmittedDate;
      $row['EndDate']=$result1->EndDate;
      $row['ApprovalDate']=$result1->ApprovalDate;
      $row['Status']=$result1->Status;
      $row['AdvCirNo']=$result1->AdvCirNo;
      $row['TA']=$result1->TA;
      $row['AdDateChosen']=$result1->AdDateChosen;
      $row['URL']=$result1->URL;

      $designations_array=array();

      $queryProjDesg = $con->select('RecruitmentProjectDetails', 'rpd');
      $queryProjDesg->fields('rpd',array('ProjectSrNo'));
      $queryProjDesg->condition('rpd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $resultProjDesg = $queryProjDesg->execute()->fetchAll();

      foreach ($resultProjDesg as $resultProjDesg1) {
        $queryDesg = $con->select('RecruitmentDesignationDetails', 'rdd');
        $queryDesg->fields('rdd',array('DesgSrNo'));
        $queryDesg->condition('rdd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
        $queryDesg->condition('rdd.ProjectSrNo', $resultProjDesg1->ProjectSrNo, "=");
        $resultDesg = $queryDesg->execute()->fetchAll();

        foreach ($resultDesg as $resultDesg1) {
          array_push($designations_array, array('DescNewTitle'=>$result1->RecruitmentSrNo.':'.$resultProjDesg1->ProjectSrNo.':'.$resultDesg1->DesgSrNo));
        }
      }

      $result1->designation_list=$designations_array;
      $row['designation_list']=$result1->designation_list;

      //echo '<pre>';
      //print_r($result1);

      \Drupal\Core\Database\Database::setActiveConnection();

      // migrate needs the date as a UNIX timestamp 
      /*try { 
      // put your source data's time zone here, or just use strtotime() if it's already in UTC 
      $d = new \DateTime($date, new \DateTimeZone('America/Los_Angeles')); 
        $row['date'] = $d->format('U'); 
      } catch (\Exception $e) { 
        echo "Exception: " . $e->getMessage() . "\n"; 
        $row['date'] = time();  // fallback – set it to now so we don't have errors 
      }*/ 
        
      // print_r(new \ArrayIterator($row)); 
      // append it to the array of rows we can import 
      $rows[] = $row; 
    } 
 
    // Migrate needs an Iterator class, not just an array
    return new \ArrayIterator($rows); 
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array( 
      'nid' => $this->t('nid'),
      'title' => $this->t('RecruitmentSrNo'),
      'body' => $this->t('body'),
      'RecruitmentSrNo' => $this->t('RecruitmentSrNo'),
      'RecruitmentType' => $this->t('RecruitmentType'),
      'PICode' => $this->t('PICode'),
      'DeptCode' => $this->t('DeptCode'),
      'SubmittedDate' => $this->t('SubmittedDate'),
      'EndDate' => $this->t('EndDate'),
      'ApprovalDate' => $this->t('ApprovalDate'),
      'Status' => $this->t('Status'),
      'AdvCirNo' => $this->t('AdvCirNo'),
      'TA' => $this->t('TA'),
      'AdDateChosen' => $this->t('AdDateChosen'),
      'URL' => $this->t('URL'),
      'designation_list' => $this->t('designation_list')
    );
  }

  public function prepareRow(Row $row) { 
    $title = $row->getSourceProperty('title');  
    // make sure the title isn't too long for Drupal 
    if (strlen($title) > 255) { 
      $row->setSourceProperty('title', substr($title,0,255)); 
    }  
    return parent::prepareRow($row); 
  } 

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [ 
      'title' => [ 
        'type' => 'string' 
      ] 
    ]; 
    return $ids; 
  }

  public function __toString() { 
    return "json data"; 
  } 
}
