<?php

/**
 * Contest
 * 
 * Contest class is used  for get contest entry,  create contest.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class Contest  {
  
  public $image;
  public $contestSlug;
  public $entryId = '';
  public $sort = '';
  public $offset = 0;
  public $count = 1;


  /**
   * getContestSubmission
   * 
   * This functiction calls getEntry method (in AggregatorManager) to get entries for a contest.
   * @return (array)
   */
  public function getContestSubmission() {
    $contestEntries = array();
    $aggregatorManager = new AggregatorManager();    
    $contestEntries = $aggregatorManager->getEntry(ENTRY_LIMIT, $this->offset, $this->entryId, 'active',
            $this->contestSlug.'[contest]', '', '', $this->count, '', '', '', '',array(), $this->sort,
            'links,status,author,title,id,content,tags','','');
    $i = 0;
    foreach ($contestEntries as $entry) {
      if (!empty($entry['links']['enclosures'])) { 
        $contestEntries[$i]['image'] =  $entry['links']['enclosures'][0]['uri'] ;
        $i++;
      }
    }
    return $contestEntries;
  }
  
  /**
   * createContest
   * 
   * function is used for create contest
   * @param (string) $imagePath
   * @param (string) $squareImage
   * @return (array) $response
   */
  
  public function createContest($imagePath, $squareImage) {
    $contestAPI = new ContestAPI();
    $response = array();
    $contestDetails = array();
    $contestDetails = $_POST;
    try {
      if (!empty($contestDetails)) {
        if(array_key_exists('contestTitle', $contestDetails) && empty($contestDetails['contestTitle'])) {
          throw new Exception(Yii::t('contest', 'Contest title should not be empty'));
        } 
        if(array_key_exists('startDate', $contestDetails) && empty($contestDetails['startDate'])) {
          throw new Exception(Yii::t('contest', 'Start date should not be empty'));
        } else if (!validateDate($contestDetails['startDate'])){
          throw new Exception(Yii::t('contest', 'Please enter valid start date'));
        } else {
          $startDateArr = explode('/', $contestDetails['startDate']);
          $startTime = mktime(0, 0, 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contestAPI->startDate = date('Y-m-d H:i:s', $startTime);
        }
        if(array_key_exists('endDate', $contestDetails) && empty($contestDetails['endDate'])) {
          throw new Exception(Yii::t('contest', 'End date should not be empty'));
        }  else if (!validateDate($contestDetails['endDate'])){
          throw new Exception(Yii::t('contest','Please enter valid end date'));
        } else {
          $endDateArr = explode('/', $contestDetails['endDate']);
          $endTime = mktime(0, 0, 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contestAPI->endDate = date('Y-m-d H:i:s', $endTime);
        }
        if(array_key_exists('contestDescription', $contestDetails) && empty($contestDetails['contestDescription'])) {
          throw new Exception(Yii::t('contest', 'Contest description should not be empty'));
        }
        if (empty($imagePath)) {
          throw new Exception(Yii::t('contest', 'Please provide banner image'));
        }
        if (empty($squareImage)) {
          throw new Exception(Yii::t('contest', 'Please provide square image'));
        }
        if(array_key_exists('contestRule', $contestDetails) && empty($contestDetails['contestRule'])) {
          throw new Exception(Yii::t('contest', 'Contest rule should not be empty'));
        }
        if (array_key_exists('showEntry', $contestDetails) && !empty($contestDetails['showEntry'])) {
          $contestAPI->entryStatus = true;
        }
        $contestAPI->contestTitle = $contestDetails['contestTitle'];
        $contestAPI->contestDescription = $contestDetails['contestDescription'];        
        $contestAPI->creationDate = date('Y-m-d H:i:s'); 
        $contestAPI->contestSlug =  strtolower(preg_replace("/[^a-z0-9]+/i", "_", $contestDetails['contestTitle']));        
        $contestAPI->contestImage = $imagePath;
        $contestAPI->squareImage = $squareImage;
        $contestAPI->contestRule = $contestDetails['contestRule'];
       
        
        //check for contest exist
        $exist = $contestAPI->getContestDetailByContestSlug();
        if ($exist) {
          throw new Exception(Yii::t('contest', 'This contest title is already exist'));
        }
        $response['success'] = $contestAPI->save();
        $response['msg'] = Yii::t('contest', 'You have created a contest Succesfully');
      }
    } catch (Exception $e) {
       Yii::log('', ERROR, Yii::t('contest', 'Error in createContest :') . $e->getMessage());
       $response['success'] = '';
       $response['msg'] = $e->getMessage();
    }
    return $response;
  }
  
  /**
   * getContestDetail
   * 
   * This function is used for get contest and manipulate it
   */
  
  public function getContestDetail() {
    $contestAPI = new ContestAPI();
    $contestDetail = array();
   
    if (!empty($this->contestSlug)) {
      $contestAPI->contestSlug = $this->contestSlug;      
      $contestDetail = $contestAPI->getContestDetailByContestSlug(); 
      if(array_key_exists('startDate', $contestDetail) && !empty($contestDetail['startDate'])) {
        $contestDetail['startDate'] = date('Y-m-d', strtotime($contestDetail['startDate']));
      }
      if(array_key_exists('endDate', $contestDetail) && !empty($contestDetail['endDate'])) {
        $contestDetail['endDate'] = date('Y-m-d', strtotime($contestDetail['endDate']));
      }
    } else {
      $contestDetail = $contestAPI->getContestDetail();
    }
    return $contestDetail;
  }
  
  /**
   * submitContestEntry
   * 
   * This function is used for submit entry
   * @param (string) $imagePath
   * @param (string) $contestSlug
   * @return (array) $response
   */
  public function submitContestEntry($imagePath, $contestSlug) {
    $response['success'] = false;
    $entrySubmittedByUser = false;
    try {  
      if (!empty($_POST)) {
        $aggregatorManager = new AggregatorManager();
        $aggregatorManager->isMinor = ADULT;
        if (array_key_exists('entryTitle', $_POST) && (!empty($_POST['entryTitle']))) {
          $aggregatorManager->entryTitle =  $_POST['entryTitle'];
        }
        if(array_key_exists('entryDescription', $_POST) && (!empty($_POST['entryDescription']))) {
          $aggregatorManager->entryDescription = $_POST['entryDescription'];
        }
        if (!array_key_exists('leftCheckBox', $_POST)  && (!array_key_exists('rightCheckBox', $_POST))) {
          throw new Exception(Yii::t('contest','Please checked one check box'));
        }
        
        if (array_key_exists('rightCheckBox', $_POST)) {
          if (empty($_POST['minorName'])) {
            throw new Exception(Yii::t('contest','Please enter minor name'));
          } else {
            $aggregatorManager->minorName = $_POST['minorName'];
            $aggregatorManager->isMinor = MINOR;
          }
        } 
        
        if (array_key_exists('minorName', $_POST) && !empty($_POST['minorName'])) {
          $aggregatorManager->category = 'minor';
        } else {
          $aggregatorManager->category = 'adult';
        }
        
        $aggregatorManager->authorName = Yii::app()->session['user']['firstname'].' '. Yii::app()->session['user']['lastname'];
        $aggregatorManager->authorSlug = Yii::app()->session['user']['id'];
        $aggregatorManager->imageUrl =  BASE_URL . $imagePath;
        $aggregatorManager->contestSlug = $contestSlug;
        $aggregatorManager->contestName = $_POST['contestTitle'];
        
        
          $response = $aggregatorManager->saveEntry();
          if ($response['success']) {
            $response['msg'] = Yii::t('contest', 'You have succesfully submit an entry');
          } else {
            $response['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');
          }
        
      }
    } catch (Exception $e) {
       Yii::log('', ERROR, Yii::t('contest', 'Error in submitContestEntry :') . $e->getMessage());
       $response['success'] = false;
       $response['msg'] = $e->getMessage();
    }
    return $response;
  }
  
   /**
   * getContestSubmissionInfo
   * 
   * This functiction calls getEntry method (in AggregatorManager) to get single entry for a contest.
   * @return (array)
   */
  public function getContestSubmissionInfo() {
    $contestEntries = array();
    $entry = array(); 
    $aggregatorManager = new AggregatorManager();
    $contestEntries = $aggregatorManager->getEntry('', '', $this->entryId, 'active', $this->contestSlug . '[contest]', '', '', 
            $this->count, '', '', '', '', array(), $this->sort, 'links,status,author,title,id,content', '', '');
    $entry = $contestEntries[0]; 
    if (!empty($entry['links']['enclosures'])) {
      $entry['image'] = $entry['links']['enclosures'][0]['uri'];
    }
    return $entry;
  }

}
