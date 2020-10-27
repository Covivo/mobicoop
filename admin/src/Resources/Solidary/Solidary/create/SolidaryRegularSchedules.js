import React, { useEffect, useState } from 'react';
import Box from '@material-ui/core/Box';
import { Button, Card, Grid } from '@material-ui/core';
import PropTypes from 'prop-types';
import DeleteIcon from '@material-ui/icons/Delete';
import { makeStyles } from '@material-ui/core/styles';
import { useField } from 'react-final-form';
import { DateInput, required } from 'react-admin';
import DayChipInput from './DayChipInput';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
  },
  dayChip: {
    margin: '0.5rem',
  },
  times: {
    display: 'flex',
    justifyContent: 'space-between',
    margin: '0.5rem',
    width: '25%',
  },
  spaceRight: {
    marginRight: '6rem',
  },
  none: {
    display: 'none',
  },
}));

const BoundedDateTimeField = (props) => {
  const {
    input: { value, name, onChange, ...rest },
  } = useField(props.source);
  const { forcedValue, onChange: onChangeInput } = props;

  useEffect(() => {
    if (forcedValue !== undefined) {
      onChange(forcedValue);
    }
  }, [forcedValue]);

  return (
    <DateInput
      name={name}
      type="time"
      InputLabelProps={{ shrink: true }}
      label={props.label}
      validate={props.required ? [required()] : []}
      onChange={(event) => {
        onChangeInput(event.target.value);
        return onChange(event.target.value);
      }}
      {...rest}
    />
  );
};

BoundedDateTimeField.defaultProps = {
  onChange: () => {},
  forcedValue: undefined,
  required: false,
};

BoundedDateTimeField.propTypes = {
  onChange: PropTypes.func,
  forcedValue: PropTypes.string,
  source: PropTypes.string.isRequired,
  label: PropTypes.string.isRequired,
  required: PropTypes.bool,
};

const Ask = ({
  outwardTimes,
  setOutwardTimes: setOutwardTimesTemplate,
  returnTimes,
  setReturnTimes: setReturnTimesTemplate,
  id,
  setDays: setDaysTemplate,
  slot,
  slotsList,
  setSlotsList,
}) => {
  const classes = useStyles();

  const updateSlotsDays = (index, day, value) => {
    const newArr = [...slotsList];
    newArr[index].days[day] = value;

    setSlotsList(newArr);
  };

  const checkDaysTemplate = (day, value) => {
    let newValue = value;
    if (!value) {
      slotsList.forEach((s) => {
        if (s.days[day]) {
          newValue = true;
        }
      });
    }
    setDaysTemplate((prevState) => ({
      ...prevState,
      [day]: newValue,
    }));
  };

  useEffect(() => {
    if (slot.returnTimes) {
      setReturnTimes(slot.returnTimes);
    }
    if (slot.outwardTimes) {
      setOutwardTimes(slot.outwardTimes);
    }
  }, [slotsList]);

  const setReturnTimes = (value) => {
    setReturnTimesTemplate((prevState) => ({
      ...prevState,
      mon: slot.days.mon ? value : returnTimes.mon,
      tue: slot.days.tue ? value : returnTimes.tue,
      wed: slot.days.wed ? value : returnTimes.wed,
      thu: slot.days.thu ? value : returnTimes.thu,
      fri: slot.days.fri ? value : returnTimes.fri,
      sat: slot.days.sat ? value : returnTimes.sat,
      sun: slot.days.sun ? value : returnTimes.sun,
    }));
  };

  const setOutwardTimes = (value) => {
    setOutwardTimesTemplate((prevState) => ({
      ...prevState,
      mon: slot.days.mon ? value : outwardTimes.mon,
      tue: slot.days.tue ? value : outwardTimes.tue,
      wed: slot.days.wed ? value : outwardTimes.wed,
      thu: slot.days.thu ? value : outwardTimes.thu,
      fri: slot.days.fri ? value : outwardTimes.fri,
      sat: slot.days.sat ? value : outwardTimes.sat,
      sun: slot.days.sun ? value : outwardTimes.sun,
    }));
  };

  return (
    <>
      <div className={classes.dayChip}>
        <Box>
          <DayChipInput
            label="L"
            source={`day.mon${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'mon', value);
              checkDaysTemplate('mon', value);
            }}
          />
          <DayChipInput
            label="Ma"
            source={`day.tue${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'tue', value);
              checkDaysTemplate('tue', value);
            }}
          />
          <DayChipInput
            label="Me"
            source={`day.wed${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'wed', value);
              checkDaysTemplate('wed', value);
            }}
          />
          <DayChipInput
            label="J"
            source={`day.thu${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'thu', value);
              checkDaysTemplate('thu', value);
            }}
          />
          <DayChipInput
            label="V"
            source={`day.fri${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'fri', value);
              checkDaysTemplate('fri', value);
            }}
          />
          <DayChipInput
            label="S"
            source={`day.sat${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'sat', value);
              checkDaysTemplate('sat', value);
            }}
          />
          <DayChipInput
            label="D"
            source={`day.sun${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'sun', value);
              checkDaysTemplate('sun', value);
            }}
          />
        </Box>
      </div>
      <div className={classes.times}>
        <BoundedDateTimeField
          source={`outwardTimes${slot.id}`}
          label="Départ à"
          onChange={(value) => {
            setOutwardTimes(value);
            const newArr = [...slotsList];
            newArr[id].outwardTimes = value;
            setSlotsList(newArr);
          }}
          required={id === 0}
        />
        <BoundedDateTimeField
          source={`returnTimes${slot.id}`}
          label="Retour à"
          onChange={(value) => {
            setReturnTimes(value);
            const newArr = [...slotsList];
            newArr[id].returnTimes = value;
            setSlotsList(newArr);
          }}
        />
      </div>
    </>
  );
};

Ask.propTypes = {
  outwardTimes: PropTypes.object.isRequired,
  setOutwardTimes: PropTypes.func.isRequired,
  returnTimes: PropTypes.object.isRequired,
  setReturnTimes: PropTypes.func.isRequired,
  id: PropTypes.number.isRequired,
  setDays: PropTypes.func.isRequired,
  slot: PropTypes.object.isRequired,
  slotsList: PropTypes.array.isRequired,
  setSlotsList: PropTypes.func.isRequired,
};

const SolidaryRegularSchedules = () => {
  const [days, setDays] = useState({
    mon: false,
    tue: false,
    wed: false,
    thu: false,
    fri: false,
    sat: false,
    sun: false,
  });
  const [outwardTimes, setOutwardTimes] = useState({
    mon: null,
    tue: null,
    wed: null,
    thu: null,
    fri: null,
    sat: null,
    sun: null,
  });
  const [returnTimes, setReturnTimes] = useState({
    mon: null,
    tue: null,
    wed: null,
    thu: null,
    fri: null,
    sat: null,
    sun: null,
  });

  const [slotsList, setSlotsList] = useState([
    {
      id: 0,
      days: { mon: false, tue: false, wed: false, thu: false, fri: false, sat: false, sun: false },
      outwardTimes: null,
      returnTimes: null,
    },
  ]);
  const classes = useStyles();

  const [id, setId] = useState(1);

  const onAddBtnClick = () => {
    setId((prevState) => prevState + 1);
    setSlotsList([
      ...slotsList,
      {
        id,
        days: {
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
        },
        outwardTimes: null,
        returnTimes: null,
      },
    ]);
  };

  const checkReturnTemplate = (day) => {
    let value = null;
    slotsList.forEach((s) => {
      if (s.days[day]) {
        if (s.returnTimes) {
          value = s.returnTimes;
        }
      }
    });
    setReturnTimes((prevState) => ({
      ...prevState,
      [day]: value,
    }));
  };

  const checkOutwardTemplate = (day) => {
    let value = null;
    slotsList.forEach((s) => {
      if (s.days[day]) {
        if (s.outwardTimes) {
          value = s.outwardTimes;
        }
      }
    });
    setOutwardTimes((prevState) => ({
      ...prevState,
      [day]: value,
    }));
  };

  const checkDaysTemplate = (day) => {
    let value = false;
    slotsList.forEach((s) => {
      if (s.days[day]) {
        value = true;
      }
    });
    setDays((prevState) => ({
      ...prevState,
      [day]: value,
    }));
  };

  useEffect(() => {
    const iterator = Object.keys(days);
    for (const key of iterator) {
      checkDaysTemplate(key);
      checkOutwardTemplate(key);
      checkReturnTemplate(key);
    }
  }, [slotsList]);

  const checkTimesTemplate = (day, slots) => {
    let newOutward = null;
    let newReturn = null;
    slots.forEach((s) => {
      if (s.days[day]) {
        if (s.outwardTimes) {
          newOutward = s.outwardTimes;
        }
        if (s.returnTimes) {
          newReturn = s.returnTimes;
        }
      }
    });
    setOutwardTimes((prevState) => ({
      ...prevState,
      [day]: newOutward,
    }));
    setReturnTimes((prevState) => ({
      ...prevState,
      [day]: newReturn,
    }));
  };

  const onRemoveBtnClick = (toDelete) => {
    const slots = slotsList.filter((slot) => slot.id !== toDelete.id);
    setSlotsList(slots);
    for (const [key, value] of Object.entries(toDelete.days)) {
      if (value) {
        checkTimesTemplate(key, slots);
      }
    }
  };

  return (
    <>
      <div className={classes.none}>
        <DayChipInput source="days.mon" label="L" forcedValue={days.mon} />
        <DayChipInput source="days.tue" label="Ma" forcedValue={days.tue} />
        <DayChipInput source="days.wed" label="Me" forcedValue={days.wed} />
        <DayChipInput source="days.thu" label="J" forcedValue={days.thu} />
        <DayChipInput source="days.fri" label="V" forcedValue={days.fri} />
        <DayChipInput source="days.sat" label="S" forcedValue={days.sat} />
        <DayChipInput source="days.sun" label="D" forcedValue={days.sun} />
        <BoundedDateTimeField
          source="outwardTimes.mon"
          label="Départ à"
          forcedValue={outwardTimes.mon}
        />
        <BoundedDateTimeField
          source="outwardTimes.tue"
          label="Départ à"
          forcedValue={outwardTimes.tue}
        />
        <BoundedDateTimeField
          source="outwardTimes.wed"
          label="Départ à"
          forcedValue={outwardTimes.wed}
        />
        <BoundedDateTimeField
          source="outwardTimes.thu"
          label="Départ à"
          forcedValue={outwardTimes.thu}
        />
        <BoundedDateTimeField
          source="outwardTimes.fri"
          label="Départ à"
          forcedValue={outwardTimes.fri}
        />
        <BoundedDateTimeField
          source="outwardTimes.sat"
          label="Départ à"
          forcedValue={outwardTimes.sat}
        />
        <BoundedDateTimeField
          source="outwardTimes.sun"
          label="Départ à"
          forcedValue={outwardTimes.sun}
        />

        <BoundedDateTimeField
          source="returnTimes.mon"
          label="Retour à"
          forcedValue={returnTimes.mon}
        />
        <BoundedDateTimeField
          source="returnTimes.tue"
          label="Retour à"
          forcedValue={returnTimes.tue}
        />
        <BoundedDateTimeField
          source="returnTimes.wed"
          label="Retour à"
          forcedValue={returnTimes.wed}
        />
        <BoundedDateTimeField
          source="returnTimes.thu"
          label="Retour à"
          forcedValue={returnTimes.thu}
        />
        <BoundedDateTimeField
          source="returnTimes.fri"
          label="Retour à"
          forcedValue={returnTimes.fri}
        />
        <BoundedDateTimeField
          source="returnTimes.sat"
          label="Retour à"
          forcedValue={returnTimes.sat}
        />
        <BoundedDateTimeField
          source="returnTimes.sun"
          label="Retour à"
          forcedValue={returnTimes.sun}
        />
      </div>
      <div>
        {slotsList.map((slot, i) => {
          return (
            <Card raised className={classes.card} key={`card-${slot.id}`}>
              <Ask
                outwardTimes={outwardTimes}
                setOutwardTimes={setOutwardTimes}
                returnTimes={returnTimes}
                setReturnTimes={setReturnTimes}
                id={i}
                setDays={setDays}
                slotsList={slotsList}
                setSlotsList={setSlotsList}
                slot={slot}
              />
              {slotsList.length > 1 && (
                <Grid item xs={2}>
                  <Button
                    color="secondary"
                    startIcon={<DeleteIcon />}
                    onClick={() => onRemoveBtnClick(slot)}
                  >
                    Supprimer
                  </Button>
                </Grid>
              )}
            </Card>
          );
        })}
        <Button onClick={onAddBtnClick}>Ajouter un créneau</Button>
      </div>
    </>
  );
};

export default SolidaryRegularSchedules;
