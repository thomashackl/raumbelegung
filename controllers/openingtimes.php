<?php

class OpeningTimesController extends StudipController {

    /**
     * This method is called before every other action.
     */
    public function before_filter(&$action, &$args)
    {
        $GLOBALS['perm']->check('root');

        parent::before_filter($action, $args);

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/schedule-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(dgettext('roomplanplugin', 'Öffnungszeiten verwalten'), $this->url_for('openingtimes/times'))
            ->setActive($action == 'times');
        $views->addLink(dgettext('roomplanplugin', 'Raumzuweisung'), $this->url_for('openingtimes/rooms'))
            ->setActive($action == 'rooms');
        $sidebar->addWidget($views);
    }

    /**
     * Show buildings and their opening times.
     */
    public function times_action()
    {
        Navigation::activateItem('/calendar/raumbelegung/openingtimes');
        PageLayout::setTitle($this->dispatcher->plugin->getDisplayName() . ' - ' .
            dgettext('roomplanplugin', 'Gebäudeöffnungszeiten verwalten'));

        // Find all relevant buildings.
        $this->buildings = [];
        foreach (Building::findAll() as $building) {
            $this->buildings[] = [
                'data' => $building,
                'opening_times' => ResourceOpeningTimes::find($building->id)
            ];
        }
    }

    /**
     * Configure selected rooms to use the opening times of their
     * building instead of regular assignments.
     */
    public function rooms_action()
    {
        Navigation::activateItem('/calendar/raumbelegung/openingtimes');
        PageLayout::setTitle($this->dispatcher->plugin->getDisplayName() . ' - ' .
            dgettext('roomplanplugin', 'Raumzuweisung'));

        $ids = SimpleCollection::createFromArray(ResourceObjectOpeningTimes::findBySQL("1"))->pluck('resource_id');
        $this->rooms = Room::findMany($ids, 'ORDER BY `name`');
    }

    /**
     * Stores given opening times for buildings.
     */
    public function store_times_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $times = Request::getArray('times');

        foreach ($times as $id => $data) {

            $object = ResourceOpeningTimes::find($id);

            // Store given values.
            if ($data['weekdays_start'] || $data['weekdays_end'] || $data['saturday_start'] ||
                    $data['saturday_end'] || $data['sunday_start'] || $data['sunday_end']) {

                if (!$object) {
                    $object = new ResourceOpeningTimes();
                    $object->resource_id = $id;
                }

                foreach (words('weekdays_start weekdays_end saturday_start saturday_end sunday_start sunday_end') as $value) {
                    if ($data[$value]) {
                        $object->$value = $data[$value];
                    } else {
                        $object->$value = null;
                    }
                }
                $object->store();

            // Cleanup removed entries.
            } else if ($object) {
                $object->delete();
            }
        }

        $this->relocate('openingtimes/times');
    }

    /**
     * Assign a room to use the opening times of its building.
     */
    public function assign_room_action()
    {
        $object = ResourceObjectOpeningTimes::find(Request::option('resource_id'));
        if (!$object) {
            $object = new ResourceObjectOpeningTimes();
            $object->resource_id = Request::option('resource_id');
            if ($object->store()) {
                PageLayout::postSuccess(
                    dgettext('roomplanplugin', 'Die Zuweisung wurde gespeichert.'));
            } else {
                PageLayout::postError(
                    dgettext('roomplanplugin', 'Die Zuweisung konnte nicht gespeichert werden.'));
            }
        }

        $this->relocate('openingtimes/rooms');
    }

    /**
     * Remove an assignment.
     * @param string $resource_id the room to remove
     */
    public function unassign_room_action($resource_id)
    {
        $object = ResourceObjectOpeningTimes::find($resource_id);
        if ($object) {
            if ($object->delete()) {
                PageLayout::postSuccess(
                    dgettext('roomplanplugin', 'Die Zuweisung wurde gelöscht.'));
            } else {
                PageLayout::postError(
                    dgettext('roomplanplugin', 'Die Zuweisung konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postWarning(
                dgettext('roomplanplugin', 'Es wurde keine Zuordnung für den angegebenen Raum gefunden.'));
        }
        $this->relocate('openingtimes/rooms');
    }

}
