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

      $queryRSP = $con->select('RecruitmentSelectionProcess', 'rsp');
      $queryRSP->condition('rsp.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $queryRSP->condition('rsp.ProjectSrNo', $result1->ProjectSrNo, "=");
      $queryRSP->condition('rsp.DesgSrNo', $result1->DesgSrNo, "=");
      $queryRSP->fields('rsp',array( 'Test1','Test1Date','Test1Time','Test1ReportingTime','Test1Duration','Test2','Test2Date','Test2Time','Test2ReportingTime','Test2Duration','InterviewDate','InterviewTime','InterviewReportingTime','TestVenue','InterviewVenue','TA','Selection','Remarks'));
      $resultRSP = $queryRSP->execute()->fetchAll();

      if(isset($resultRSP[0]->Test1)) {
        $row['RSP_Test1']=$resultRSP[0]->Test1;
      } else {
        $row['RSP_Test1']='';
      }
      if(isset($resultRSP[0]->Test1Date)) {
        $row['RSP_Test1Date']=$resultRSP[0]->Test1Date;
      } else {
        $row['RSP_Test1Date']='';
      }
      if(isset($resultRSP[0]->Test1Time)) {
        $row['RSP_Test1Time']=$resultRSP[0]->Test1Time;
      } else {
        $row['RSP_Test1Time']='';
      }
      if(isset($resultRSP[0]->Test1ReportingTime)) {
        $row['RSP_Test1ReportingTime']=$resultRSP[0]->Test1ReportingTime;
      } else {
        $row['RSP_Test1ReportingTime']='';
      }
      if(isset($resultRSP[0]->Test1Duration)) {
        $row['RSP_Test1Duration']=$resultRSP[0]->Test1Duration;
      } else {
        $row['RSP_Test1Duration']='';
      }
      if(isset($resultRSP[0]->Test2)) {
        $row['RSP_Test2']=$resultRSP[0]->Test2;
      } else {
        $row['RSP_Test2']='';
      }
      if(isset($resultRSP[0]->Test2Date)) {
        $row['RSP_Test2Date']=$resultRSP[0]->Test2Date;
      } else {
        $row['RSP_Test2Date']='';
      }
      if(isset($resultRSP[0]->Test2Time)) {
        $row['RSP_Test2Time']=$resultRSP[0]->Test2Time;
      } else {
        $row['RSP_Test2Time']='';
      }
      if(isset($resultRSP[0]->Test2ReportingTime)) {
        $row['RSP_Test2ReportingTime']=$resultRSP[0]->Test2ReportingTime;
      } else {
        $row['RSP_Test2ReportingTime']='';
      }
      if(isset($resultRSP[0]->Test2Duration)) {
        $row['RSP_Test2Duration']=$resultRSP[0]->Test2Duration;
      } else {
        $row['RSP_Test2Duration']='';
      }
      if(isset($resultRSP[0]->InterviewDate)) {
        $row['RSP_InterviewDate']=$resultRSP[0]->InterviewDate;
      } else {
        $row['RSP_InterviewDate']='';
      }
      if(isset($resultRSP[0]->InterviewTime)) {
        $row['RSP_InterviewTime']=$resultRSP[0]->InterviewTime;
      } else {
        $row['RSP_InterviewTime']='';
      }
      if(isset($resultRSP[0]->InterviewReportingTime)) {
        $row['RSP_InterviewReportingTime']=$resultRSP[0]->InterviewReportingTime;
      } else {
        $row['RSP_InterviewReportingTime']='';
      }
      if(isset($resultRSP[0]->TestVenue)) {
        $row['RSP_TestVenue']=$resultRSP[0]->TestVenue;
      } else {
        $row['RSP_TestVenue']='';
      }
      if(isset($resultRSP[0]->InterviewVenue)) {
        $row['RSP_InterviewVenue']=$resultRSP[0]->InterviewVenue;
      } else {
        $row['RSP_InterviewVenue']='';
      }
      if(isset($resultRSP[0]->TA)) {
        $row['RSP_TA']=$resultRSP[0]->TA;
      } else {
        $row['RSP_TA']='';
      }
      if(isset($resultRSP[0]->Selection)) {
        $row['RSP_Selection']=$resultRSP[0]->Selection;
      } else {
        $row['RSP_Selection']='';
      }
      if(isset($resultRSP[0]->Remarks)) {
        $row['RSP_Remarks']=$resultRSP[0]->Remarks;
      } else {
        $row['RSP_Remarks']='';
      }

      $queryRCD = $con->select('RecruitmentCandidateDetails', 'rcd');
      $queryRCD->condition('rcd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $queryRCD->condition('rcd.ProjectSrNo', $result1->ProjectSrNo, "=");
      $queryRCD->condition('rcd.DesgSrNo', $result1->DesgSrNo, "=");
      $queryRCD->fields('rcd',array( 'EmpName'));
      $resultRCD = $queryRCD->execute()->fetchAll();

      $shortlisted_array=array();

      foreach ($resultRCD as $resultRCD1) { 
        array_push($shortlisted_array, array('shortlisted_list_each'=>$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo.':'.$resultRCD1->EmpName));
      }
      $row['shortlisted_list']=$shortlisted_array;

      $queryRSC = $con->select('RecruitmentSelectedCandidates', 'rsc');
      $query->innerJoin('RecruitmentDetails', 'rd', 'rsc.AdvCirNo = rd.AdvCirNo');
      $query->innerJoin('RecruitmentProjectDetails', 'rpd', 'rsc.AdvJobCode = rpd.AdvJobCode OR rsc.AdvJobCode = rpd.ProCode');
      $query->innerJoin('RecruitmentDesignationDetails', 'rdd', 'rsc.DesigCode = rdd.DesgCode');
      $queryRSC->condition('rd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $queryRSC->condition('rpd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $queryRSC->condition('rdd.RecruitmentSrNo', $result1->RecruitmentSrNo, "=");
      $queryRSC->condition('rpd.ProjectSrNo', $result1->ProjectSrNo, "=");
      $queryRSC->condition('rdd.ProjectSrNo', $result1->ProjectSrNo, "=");
      $queryRSC->condition('rdd.DesgSrNo', $result1->DesgSrNo, "=");
      $queryRSC->fields('rd',array( 'RecruitmentSrNo'));
      $queryRSC->fields('rsc',array( 'ApptSno','EmpCode','CandidateName'));
      $resultRSC = $queryRSC->execute()->fetchAll();

      $appointed_array=array();

      foreach ($resultRSC as $resultRSC1) { 
        array_push($appointed_array, array('appointed_list_each'=>$result1->RecruitmentSrNo.':'.$result1->ProjectSrNo.':'.$result1->DesgSrNo.':'.$resultRSC1->ApptSno.':'.$resultRSC1->EmpCode.':'.$resultRSC1->CandidateName));
      }
      $row['appointed_list']=$appointed_array;

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

      'RSP_Test1' => $this->t('RSP_Test1'),
      'RSP_Test1Date' => $this->t('RSP_Test1Date'),
      'RSP_Test1Time' => $this->t('RSP_Test1Time'),
      'RSP_Test1ReportingTime' => $this->t('RSP_Test1ReportingTime'),
      'RSP_Test1Duration' => $this->t('RSP_Test1Duration'),
      'RSP_Test2' => $this->t('RSP_Test2'),
      'RSP_Test2Date' => $this->t('RSP_Test2Date'),
      'RSP_Test2Time' => $this->t('RSP_Test2Time'),
      'RSP_Test2ReportingTime' => $this->t('RSP_Test2ReportingTime'),
      'RSP_Test2Duration' => $this->t('RSP_Test2Duration'),
      'RSP_InterviewDate' => $this->t('RSP_InterviewDate'),
      'RSP_InterviewTime' => $this->t('RSP_InterviewTime'),
      'RSP_InterviewReportingTime' => $this->t('RSP_InterviewReportingTime'),
      'RSP_TestVenue' => $this->t('RSP_TestVenue'),
      'RSP_InterviewVenue' => $this->t('RSP_InterviewVenue'),
      'RSP_TA' => $this->t('RSP_TA'),
      'RSP_Selection' => $this->t('RSP_Selection'),
      'RSP_Remarks' => $this->t('RSP_Remarks'),

      'shortlisted_list' => $this->t('shortlisted_list'),
      'appointed_list' => $this->t('appointed_list'),
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
