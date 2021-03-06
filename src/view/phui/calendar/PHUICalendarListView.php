<?php

final class PHUICalendarListView extends AphrontTagView {

  private $events = array();
  private $blankState;
  private $view;

  private function getView() {
    return $this->view;
  }

  public function setView($view) {
    $this->view = $view;
    return $this;
  }

  public function addEvent(AphrontCalendarEventView $event) {
    $this->events[] = $event;
    return $this;
  }

  public function showBlankState($state) {
    $this->blankState = $state;
    return $this;
  }

  protected function getTagName() {
    return 'div';
  }

  protected function getTagAttributes() {
    require_celerity_resource('phui-calendar-css');
    require_celerity_resource('phui-calendar-list-css');
    return array('class' => 'phui-calendar-event-list');
  }

  protected function getTagContent() {
    if (!$this->blankState && empty($this->events)) {
      return '';
    }

    $singletons = array();
    $allday = false;
    foreach ($this->events as $event) {
      $start_epoch = $event->getEpochStart();

      if ($event->getIsAllDay()) {
        $timelabel = pht('All Day');
      } else {
        $timelabel = phabricator_time(
          $event->getEpochStart(),
          $this->getUser());
      }

      if ($event->getViewerIsInvited()) {
        $icon_color = 'green';
      } else {
        $icon_color = null;
      }

      $dot = id(new PHUIIconView())
        ->setIconFont($event->getIcon(), $icon_color)
        ->addClass('phui-calendar-list-item-icon');

      $title = phutil_tag(
        'span',
        array(
          'class' => 'phui-calendar-list-title',
        ),
        $this->getEventTitle($event, $allday));
      $time = phutil_tag(
        'span',
        array(
          'class' => 'phui-calendar-list-time',
        ),
        $timelabel);

      $class = 'phui-calendar-list-item';
      if ($event->getViewerIsInvited()) {
        $class = $class.' phui-calendar-viewer-invited';
      }
      if ($event->getIsAllDay()) {
        $class = $class.' all-day';
      }

      $tip = $this->getEventTooltip($event);
      $tip_align = ($this->getView() == 'day') ? 'E' : 'N';
      $content = javelin_tag(
        'a',
        array(
          'href' => '/E'.$event->getEventID(),
          'sigil' => 'has-tooltip',
          'meta'  => array(
            'tip'  => $tip,
            'size' => 200,
            'align' => $tip_align,
          ),
        ),
        array(
          $dot,
          $time,
          $title,
        ));

      $singletons[] = phutil_tag(
        'li',
        array(
          'class' => $class,
        ),
        $content);
    }

    if (empty($singletons)) {
      $singletons[] = phutil_tag(
        'li',
        array(
          'class' => 'phui-calendar-list-item-empty',
        ),
        pht('Clear sailing ahead.'));
    }

    $list = phutil_tag(
      'ul',
      array(
        'class' => 'phui-calendar-list',
      ),
      $singletons);

    return $list;
  }

  private function getEventTitle($event) {
    $class = 'phui-calendar-item';
    return phutil_tag(
      'span',
      array(
        'class' => $class,
      ),
      $event->getName());
  }

  private function getEventTooltip(AphrontCalendarEventView $event) {
    Javelin::initBehavior('phabricator-tooltips');

    $start = id(AphrontFormDateControlValue::newFromEpoch(
      $this->getUser(),
      $event->getEpochStart()));
    $end = id(AphrontFormDateControlValue::newFromEpoch(
      $this->getUser(),
      $event->getEpochEnd()));

    if ($event->getIsAllDay()) {
      if ($start->getValueDay() == $end->getValueDay()) {
        $tip = pht('All day');
      } else {
        $tip = pht(
          'All day, %s - %s',
          $start->getValueAsFormat('M j, Y'),
          $end->getValueAsFormat('M j, Y'));
      }
    } else {
      if ($start->getValueDay() == $end->getValueDay() &&
        $start->getValueMonth() == $end->getValueMonth() &&
        $start->getValueYear() == $end->getValueYear()) {
        $tip = pht(
          '%s - %s',
          $start->getValueAsFormat('g:i A'),
          $end->getValueAsFormat('g:i A'));
      } else {
        $tip = pht(
          '%s - %s',
          $start->getValueAsFormat('M j, Y, g:i A'),
          $end->getValueAsFormat('M j, Y, g:i A'));
      }
    }
    return $tip;
  }

  public function getIsViewerInvitedOnList() {
    foreach ($this->events as $event) {
      if ($event->getViewerIsInvited()) {
        return true;
      }
    }
    return false;
  }
}
