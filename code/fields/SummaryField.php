<?php
/**
 * SummaryField.php
 *
 * @author Bram de Leeuw
 * Date: 10/03/17
 */

namespace Broarm\EventTickets;

use ArrayList;
use CheckboxField;
use DBField;
use FormField;

/**
 * Class SummaryField
 *
 * @package Broarm\EventTickets
 */
class SummaryField extends FormField
{
    /**
     * @var bool
     */
    protected $editable = false;

    /**
     * @var Reservation
     */
    protected $reservation;

    protected $template = 'SummaryField';

    public function __construct($name, $title, Reservation $reservation, $editable = false)
    {
        $this->editable = (boolean)$editable;
        $this->reservation = $reservation;
        parent::__construct($name, $title);
    }

    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * Get a list of editable tickets
     * These have an numeric input field
     *
     * @return ArrayList
     */
    private function getEditableAttendees()
    {
        $attendees = ArrayList::create();
        /** @var Attendee $attendee */
        foreach ($this->getReservation()->Attendees() as $key => $attendee) {
            $fieldName = $this->name . "[{$attendee->ID}][TicketReceiver]";
            $attendee->TicketReceiverField = CheckboxField::create($fieldName)
                ->setAttribute('Title', _t('SummaryField.TICKET_RECEIVER_HELP', 'Send tickets to this person'));
            // Select the first by default
            if ($key === 0) {
                $attendee->TicketReceiverField->setValue(1);
            }
            $attendees->push($attendee);
        }
        return $attendees;
    }

    /**
     * Get the field customized with tickets and reservation
     *
     * @param array $properties
     *
     * @return \HTMLText|string
     */
    public function Field($properties = array())
    {
        $context = $this;
        $properties['Editable'] = $this->editable;
        $properties['Reservation'] = $this->getReservation();
        $properties['Attendees'] = $this->editable
            ? $this->getEditableAttendees()
            : $this->getReservation()->Attendees();

        if (count($properties)) {
            $context = $context->customise($properties);
        }

        $this->extend('onBeforeRender', $context);

        $result = $context->renderWith($this->getTemplates());

        if (is_string($result)) {
            $result = trim($result);
        } else {
            if ($result instanceof DBField) {
                $result->setValue(trim($result->getValue()));
            }
        }

        return $result;
    }
}