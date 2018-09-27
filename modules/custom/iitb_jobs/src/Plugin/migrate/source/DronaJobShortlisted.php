<?php

namespace Drupal\iitb_jobs\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase; 
use Drupal\migrate\Row; 

/**
 * Provides a 'DronaJobShortlisted' migrate source.
 *
 * @MigrateSource(
 *  id = "drona_job_shortlisted"
 * )
 */
class DronaJobShortlisted extends SourcePluginBase {
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
    
    $query = $con->select('RecruitmentCandidateDetails', 'rcd');
    //$query->condition('rcd.RecruitmentSrNo', "2015052", "=");
    $query->fields('rcd',array('RecruitmentSrNo','ProjectSrNo','DesgSrNo','EmpName','Address','Srno','Email','ModeOfCallLetter','MailSentFlag','ApptSno','PanNo','LoginName','HardCopySrNo'));
    
    //$query->range(0, 10);

    $result = $query->execute()->fetchAll();
//print_r($result);
    $rows = []; 
    foreach ($result as $result1) { 
        // using second argument of TRUE here because migrate needs the data to be 
        // associative arrays and not stdClass objects. 
        //$row = json_decode(file_get_contents($filename), true); // sets the title, body, etc. 
        //$row['json_filename'] = $filename;
        
      $row['title']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo.':'.$result1->ApptSno;
      $row['DesignationRef']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo;
//print_r($row['DesignationRef']);      
      //$row['body']=iconv(mb_detect_encoding($result1->JobProfile, mb_detect_order(), true), "UTF-8//IGNORE", $result1->JobProfile);

      $row['RecruitmentSrNo']=$result1->RecruitmentSrNo;
      $row['ProjectSrNo']=$result1->ProjectSrNo;
      $row['DesgSrNo']=$result1->DesgSrNo;
      $row['EmpName']=$result1->EmpName;
      $row['Address']=$result1->Address;
      $row['Srno']=$result1->Srno;
      $row['Email']=$result1->Email;
      $row['ModeOfCallLetter']=$result1->ModeOfCallLetter;
      $row['MailSentFlag']=$result1->MailSentFlag;
      $row['ApptSno']=$result1->ApptSno;
      $row['PanNo']=$result1->PanNo;
      $row['LoginName']=$result1->LoginName;
      $row['HardCopySrNo']=$result1->HardCopySrNo;      

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
      'RecruitmentSrNo' => $this->t('RecruitmentSrNo'),
      'ProjectSrNo' => $this->t('ProjectSrNo'),
      'DesgSrNo' => $this->t('DesgSrNo'),
      'EmpName' => $this->t('EmpName'),
      'Address' => $this->t('Address'),
      'Srno' => $this->t('Srno'),
      'Email' => $this->t('Email'),
      'ModeOfCallLetter' => $this->t('ModeOfCallLetter'),
      'MailSentFlag' => $this->t('MailSentFlag'),
      'ApptSno' => $this->t('ApptSno'),
      'PanNo' => $this->t('PanNo'),
      'LoginName' => $this->t('LoginName'),
      'HardCopySrNo' => $this->t('HardCopySrNo'),
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
