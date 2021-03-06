<?php

namespace Drupal\iitb_jobs\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase; 
use Drupal\migrate\Row; 

/**
 * Provides a 'DronaJobAppointed' migrate source.
 *
 * @MigrateSource(
 *  id = "drona_job_appointed"
 * )
 */
class DronaJobAppointed extends SourcePluginBase {
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

    $query = $con->select('RecruitmentSelectedCandidates', 'rsc');
    $query->innerJoin('RecruitmentDetails', 'rd', 'rsc.AdvCirNo = rd.AdvCirNo');
    $query->innerJoin('RecruitmentProjectDetails', 'rpd', 'rd.RecruitmentSrNo = rpd.RecruitmentSrNo AND (rsc.AdvJobCode = rpd.AdvJobCode OR rsc.AdvJobCode = rpd.ProCode)');
    $query->innerJoin('RecruitmentDesignationDetails', 'rdd', 'rpd.RecruitmentSrNo = rdd.RecruitmentSrNo AND rpd.ProjectSrNo = rdd.ProjectSrNo AND rsc.DesigCode = rdd.DesgCode');
    // $query->condition('rd.RecruitmentSrNo', 'rpd.RecruitmentSrNo', "=");
    // $query->condition('rpd.RecruitmentSrNo', 'rdd.RecruitmentSrNo', "=");
    // $query->condition('rpd.ProjectSrNo', 'rdd.ProjectSrNo', "=");
    // $query->condition('rsc.DesigCode', 'rdd.DesgCode', "=");
    $query->fields('rdd',array('RecruitmentSrNo','ProjectSrNo','DesgSrNo'));
    $query->fields('rsc',array('AdvCirNo','AdvJobCode','DesigCode','Specialization','CandidateName','ApptSno','EmpCode','Status','EnteredDate','EnteredBy','ApprovalDate','ApprovedBy','Remarks'));
    
    //$query->range(0, 50);
//print_r($query->execute());
    $result = $query->execute()->fetchAll();
//print_r($result);
    //\Drupal::logger('IITB Migration')->debug('<pre><code>' . print_r(count($result), TRUE) . '</code></pre>');
    $rows = []; 
    foreach ($result as $result1) { 
        // using second argument of TRUE here because migrate needs the data to be 
        // associative arrays and not stdClass objects. 
        //$row = json_decode(file_get_contents($filename), true); // sets the title, body, etc. 
        //$row['json_filename'] = $filename;
        
      $row['title']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo.':'.$result1->ApptSno.':'.$result1->EmpCode.':'.$result1->CandidateName;
      $row['DesignationRef']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo;
//print_r($row['DesignationRef']);  
      //$row['body']=iconv(mb_detect_encoding($result1->JobProfile, mb_detect_order(), true), "UTF-8//IGNORE", $result1->JobProfile);

      $row['AdvCirNo']=$result1->AdvCirNo;
      $row['AdvJobCode']=$result1->AdvJobCode;
      $row['DesigCode']=$result1->DesigCode;
      $row['Specialization']=$result1->Specialization;
      $row['CandidateName']=$result1->CandidateName;
      $row['ApptSno']=$result1->ApptSno;
      $row['EmpCode']=$result1->EmpCode;
      $row['Status']=$result1->Status;
      $row['EnteredDate']=$result1->EnteredDate;
      $row['EnteredBy']=$result1->EnteredBy;
      $row['ApprovalDate']=$result1->ApprovalDate;
      $row['ApprovedBy']=$result1->ApprovedBy;
      $row['Remarks']=$result1->Remarks;      

      \Drupal\Core\Database\Database::setActiveConnection();
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
      'title' => $this->t('RecruitmentSrNo:ProjectSrNo:DesgSrNo:ApptSno'),
      'DesignationRef' => $this->t('RecruitmentSrNo:ProjectSrNo:DesgSrNo'),
      'AdvCirNo' => $this->t('AdvCirNo'),
      'AdvJobCode' => $this->t('AdvJobCode'),
      'DesigCode' => $this->t('DesigCode'),
      'Specialization' => $this->t('Specialization'),
      'CandidateName' => $this->t('CandidateName'),
      'ApptSno' => $this->t('ApptSno'),
      'EmpCode' => $this->t('EmpCode'),
      'Status' => $this->t('Status'),
      'EnteredDate' => $this->t('EnteredDate'),
      'EnteredBy' => $this->t('EnteredBy'),
      'ApprovalDate' => $this->t('ApprovalDate'),
      'ApprovedBy' => $this->t('ApprovedBy'),
      'Remarks' => $this->t('Remarks'),
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
