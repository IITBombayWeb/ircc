<?php

namespace Drupal\iitb_jobs\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase; 
use Drupal\migrate\Row; 

/**
 * Provides a 'DronaJobDesignations' migrate source.
 *
 * @MigrateSource(
 *  id = "drona_job_designations"
 * )
 */
class DronaJobDesignations extends SourcePluginBase {
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
    
    $query = $con->select('RecruitmentDesignationDetails', 'rdd');
    $query->leftJoin('RecruitmentProjectDetails', 'rpd', 'rdd.RecruitmentSrNo = rpd.RecruitmentSrNo and rdd.ProjectSrNo = rpd.ProjectSrNo');
    $query->fields('rdd',array('RecruitmentSrNo','ProjectSrNo','DesgSrNo','DesgCode','QualificationExperience','JobProfile','NoOfPosts','ApptPeriod','Salary','Level','Specialization','Norms','NormsRemarks'));
    $query->fields('rpd',array('ProCode','Title','AdvJobCode','ProjectDescription'));
    
    //$query->condition('rdd.RecruitmentSrNo', "2015052", "=");
    //$query->range(0, 10);
    
    $result = $query->execute()->fetchAll();

    $rows = []; 
    foreach ($result as $result1) { 
        // using second argument of TRUE here because migrate needs the data to be 
        // associative arrays and not stdClass objects. 
        //$row = json_decode(file_get_contents($filename), true); // sets the title, body, etc. 
        //$row['json_filename'] = $filename;
        
      $row['title']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo;
      $row['DescNewTitle']=$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo;
      
      $row['body']=iconv(mb_detect_encoding($result1->JobProfile, mb_detect_order(), true), "UTF-8//IGNORE", $result1->JobProfile);

      $row['RecruitmentSrNo']=$result1->RecruitmentSrNo;
      $row['ProjectSrNo']=$result1->ProjectSrNo;
      $row['DesgSrNo']=$result1->DesgSrNo;
      $row['DesgCode']=$result1->DesgCode;
      $row['QualificationExperience']=iconv(mb_detect_encoding($result1->QualificationExperience, mb_detect_order(), true), "UTF-8//IGNORE", $result1->QualificationExperience);
      $row['NoOfPosts']=$result1->NoOfPosts;
      $row['ApptPeriod']=$result1->ApptPeriod;
      $row['Salary']=$result1->Salary;
      $row['Level']=$result1->Level;
      $row['Specialization']=iconv(mb_detect_encoding($result1->Specialization, mb_detect_order(), true), "UTF-8//IGNORE", $result1->Specialization);
      $row['Norms']=$result1->Norms;
      $row['NormsRemarks']=iconv(mb_detect_encoding($result1->NormsRemarks, mb_detect_order(), true), "UTF-8//IGNORE", $result1->NormsRemarks);
      $row['ProCode']=$result1->ProCode;
      $row['RPDTitle']=iconv(mb_detect_encoding($result1->Title, mb_detect_order(), true), "UTF-8//IGNORE", $result1->Title);
      $row['AdvJobCode']=$result1->AdvJobCode;
      $row['ProjectDescription']=iconv(mb_detect_encoding($result1->ProjectDescription, mb_detect_order(), true), "UTF-8//IGNORE", $result1->ProjectDescription);

      $queryPostdt = $con->select('Postdtls', 'pd');
      $queryPostdt->condition('pd.Postcode', $result1->DesgCode, "=");
      $queryPostdt->fields('pd',array('Postname','Permtemp','ProjectPost','Faculty','Salcode','ShortCode','Category','Level','FunctionalArea','ERPLongDescription','ERPShortDescription'));
      $resultPostdt = $queryPostdt->execute()->fetchAll();

      if(isset($resultPostdt[0]->Postname)) {
        $row['PD_Postname']=$resultPostdt[0]->Postname;
      } else {
        $row['PD_Postname']='';
      }
      if(isset($resultPostdt[0]->Permtemp)) {
        $row['PD_Permtemp']=$resultPostdt[0]->Permtemp;
      } else {
        $row['PD_Permtemp']='';
      }
      if(isset($resultPostdt[0]->ProjectPost)) {
        $row['PD_ProjectPost']=$resultPostdt[0]->ProjectPost;
      } else {
        $row['PD_ProjectPost']='';
      }
      if(isset($resultPostdt[0]->Faculty)) {
        $row['PD_Faculty']=$resultPostdt[0]->Faculty;
      } else {
        $row['PD_Faculty']='';
      }
      if(isset($resultPostdt[0]->Salcode)) {
        $row['PD_Salcode']=$resultPostdt[0]->Salcode;
      } else {
        $row['PD_Salcode']='';
      }
      if(isset($resultPostdt[0]->ShortCode)) {
        $row['PD_ShortCode']=$resultPostdt[0]->ShortCode;
      } else {
        $row['PD_ShortCode']='';
      }
      if(isset($resultPostdt[0]->Category)) {
        $row['PD_Category']=$resultPostdt[0]->Category;
      } else {
        $row['PD_Category']='';
      }
      if(isset($resultPostdt[0]->Level)) {
        $row['PD_Level']=$resultPostdt[0]->Level;
      } else {
        $row['PD_Level']='';
      }
      if(isset($resultPostdt[0]->FunctionalArea)) {
        $row['PD_FunctionalArea']=$resultPostdt[0]->FunctionalArea;
      } else {
        $row['PD_FunctionalArea']='';
      }
      if(isset($resultPostdt[0]->ERPLongDescription)) {
        $row['PD_ERPLongDescription']=iconv(mb_detect_encoding($resultPostdt[0]->ERPLongDescription, mb_detect_order(), true), "UTF-8//IGNORE", $resultPostdt[0]->ERPLongDescription);
      } else {
        $row['PD_ERPLongDescription']='';
      }
      if(isset($resultPostdt[0]->ERPShortDescription)) {
        $row['PD_ERPShortDescription']=iconv(mb_detect_encoding($resultPostdt[0]->ERPShortDescription, mb_detect_order(), true), "UTF-8//IGNORE", $resultPostdt[0]->ERPShortDescription);
      } else {
        $row['PD_ERPShortDescription']='';
      }

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
      'title' => $this->t('RecruitmentSrNo:ProjectSrNo:DesgSrNo'),
      'DescNewTitle' => $this->t('RecruitmentSrNo:ProjectSrNo:DesgSrNo'),
      'body' => $this->t('JobProfile'),
      'RecruitmentSrNo' => $this->t('RecruitmentSrNo'),
      'ProjectSrNo' => $this->t('ProjectSrNo'),
      'DesgSrNo' => $this->t('DesgSrNo'),
      'DesgCode' => $this->t('DesgCode'),
      'QualificationExperience' => $this->t('QualificationExperience'),
      'NoOfPosts' => $this->t('NoOfPosts'),
      'ApptPeriod' => $this->t('ApptPeriod'),
      'Salary' => $this->t('Salary'),
      'Level' => $this->t('Level'),
      'Specialization' => $this->t('Specialization'),
      'Norms' => $this->t('Norms'),
      'NormsRemarks' => $this->t('NormsRemarks'),
      'ProCode' => $this->t('ProCode'),
      'RPDTitle' => $this->t('Title'),
      'AdvJobCode' => $this->t('AdvJobCode'),
      'ProjectDescription' => $this->t('ProjectDescription'),

      'PD_Postname' => $this->t('PD_Postname'),
      'PD_Permtemp' => $this->t('PD_Permtemp'),
      'PD_ProjectPost' => $this->t('PD_ProjectPost'),
      'PD_Faculty' => $this->t('PD_Faculty'),
      'PD_Salcode' => $this->t('PD_Salcode'),
      'PD_ShortCode' => $this->t('PD_ShortCode'),
      'PD_Category' => $this->t('PD_Category'),
      'PD_Level' => $this->t('PD_Level'),
      'PD_FunctionalArea' => $this->t('PD_FunctionalArea'),
      'PD_ERPLongDescription' => $this->t('PD_ERPLongDescription'),
      'PD_ERPShortDescription' => $this->t('PD_ERPShortDescription'),
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
