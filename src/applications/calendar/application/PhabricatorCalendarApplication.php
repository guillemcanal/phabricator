<?php

final class PhabricatorCalendarApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Calendar');
  }

  public function getShortDescription() {
    return pht('Upcoming Events');
  }

  public function getFlavorText() {
    return pht('Never miss an episode ever again.');
  }

  public function getBaseURI() {
    return '/calendar/';
  }

  public function getFontIcon() {
    return 'fa-calendar';
  }

  public function getTitleGlyph() {
    // Unicode has a calendar character but it's in some distant code plane,
    // use "keyboard" since it looks vaguely similar.
    return "\xE2\x8C\xA8";
  }

  public function isPrototype() {
    return true;
  }

  public function getRemarkupRules() {
    return array(
      new PhabricatorCalendarRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/E(?P<id>[1-9]\d*)' => 'PhabricatorCalendarEventViewController',
      '/calendar/' => array(
        '(?:query/(?P<queryKey>[^/]+)/(?:(?P<year>\d+)/'.
          '(?P<month>\d+)/)?(?:(?P<day>\d+)/)?)?'
          => 'PhabricatorCalendarEventListController',
        'icon/(?P<id>[1-9]\d*)/'
          => 'PhabricatorCalendarEventEditIconController',
        'icon/'
          => 'PhabricatorCalendarEventEditIconController',
        'event/' => array(
          'create/'
            => 'PhabricatorCalendarEventEditController',
          'edit/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventEditController',
          'cancel/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventCancelController',
          '(?P<action>join|decline|accept)/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventJoinController',
          'comment/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventCommentController',
        ),
      ),
    );
  }

  public function getQuickCreateItems(PhabricatorUser $viewer) {
    $items = array();

    $item = id(new PHUIListItemView())
      ->setName(pht('Calendar Event'))
      ->setIcon('fa-calendar')
      ->setHref($this->getBaseURI().'event/create/');
    $items[] = $item;

    return $items;
  }

  public function getMailCommandObjects() {
    return array(
      'event' => array(
        'name' => pht('Email Commands: Events'),
        'header' => pht('Interacting with Calendar Events'),
        'object' => new PhabricatorCalendarEvent(),
        'summary' => pht(
          'This page documents the commands you can use to interact with '.
          'events in Calendar. These commands work when creating new tasks '.
          'via email and when replying to existing tasks.'),
      ),
    );
  }

}
