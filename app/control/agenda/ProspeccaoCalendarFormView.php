<?php
/**
 * ProspeccaoCalendarForm Form
 * @author  <your name here>
 */
class AgendaCalendarFormView extends TPage
{
    private $fc;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->fc = new TFullCalendar(date('Y-m-d'), 'month');
        $this->fc->enableDays([1,2,3,4,5,6]);
        $this->fc->setReloadAction(new TAction(array($this, 'getEvents')));
        $this->fc->setDayClickAction(new TAction(array('AgendaCalendarFormView', 'onStartEdit')));
        $this->fc->setEventClickAction(new TAction(array('AgendaCalendarFormView', 'onEdit')));
        $this->fc->setEventUpdateAction(new TAction(array('AgendaCalendarFormView', 'onUpdateEvent')));
        $this->fc->setTimeRange('07:00', '19:00');
        parent::add( $this->fc );
    }

    /**
     * Output events as an json
     */
    public static function getEvents($param=NULL)
    {
        $return = array();
        try
        {
            TTransaction::open('venda');
            $events = Prospeccao::where('horario_inicial', '>=', $param['start'] . ' 00:00:00')
                                ->where('horario_final',   '<=', $param['end']   . ' 23:59:59')
                                ->load();

            if ($events)
            {
                foreach ($events as $event)
                {
                    $event_array = $event->toArray();
                    $event_array['start'] = str_replace( ' ', 'T', $event_array['horario_inicial']);
                    $event_array['end']   = str_replace( ' ', 'T', $event_array['horario_final']);
                    $event_array['color'] = $event_array['cor'];
                    $event_array['title'] = $event_array['titulo'];
                    $return[] = $event_array;
                }
            }
            TTransaction::close();
            echo json_encode($return);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Reconfigure the calendar
     */
    public function onReload($param = null)
    {
        if (isset($param['view']))
        {
            $this->fc->setCurrentView($param['view']);
        }

        if (isset($param['date']))
        {
            $this->fc->setCurrentDate($param['date']);
        }
    }
}

