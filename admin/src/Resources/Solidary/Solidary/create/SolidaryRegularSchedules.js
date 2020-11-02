import React, { useEffect, useRef, useState } from 'react';
import Box from '@material-ui/core/Box';
import { Button, Card, Grid } from '@material-ui/core';
import PropTypes from 'prop-types';
import DeleteIcon from '@material-ui/icons/Delete';
import { makeStyles } from '@material-ui/core/styles';
import { useField } from 'react-final-form';
import { DateInput, required } from 'react-admin';
import DayChipInput from './DayChipInput';
import {DateTimeSelector} from "./DateTimeSelector";

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
  const { forcedValue, onChange: onChangeInput, initialValue } = props;

  useEffect(() => {
    if (forcedValue !== undefined) {
      onChange(forcedValue);
    }
  }, [forcedValue]);

  useEffect(() => {
    console.log('[EDITION] InitialValue Time', initialValue);
  }, [initialValue])

  return (
    <DateInput
      name={name}
      type="time"
      value={initialValue}
      source={props.source}
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
  initialValue: undefined,
  required: false,
};

BoundedDateTimeField.propTypes = {
  onChange: PropTypes.func,
  forcedValue: PropTypes.string,
  source: PropTypes.string.isRequired,
  label: PropTypes.string.isRequired,
  required: PropTypes.bool,
  initialValue: PropTypes.bool,
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
  choices,
  setChoice,
  setSelectedDateTime,
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
    if (value) {
      setChoice(choices[0]);
      setSelectedDateTime(value);
    } else {
      setChoice(choices[1]);
    }
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
            initialValue={slot.days.mon}
          />
          <DayChipInput
            label="Ma"
            source={`day.tue${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'tue', value);
              checkDaysTemplate('tue', value);
            }}
            initialValue={slot.days.tue}
          />
          <DayChipInput
            label="Me"
            source={`day.wed${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'wed', value);
              checkDaysTemplate('wed', value);
            }}
            initialValue={slot.days.wed}
          />
          <DayChipInput
            label="J"
            source={`day.thu${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'thu', value);
              checkDaysTemplate('thu', value);
            }}
            initialValue={slot.days.thu}
          />
          <DayChipInput
            label="V"
            source={`day.fri${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'fri', value);
              checkDaysTemplate('fri', value);
            }}
            initialValue={slot.days.fri}
          />
          <DayChipInput
            label="S"
            source={`day.sat${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'sat', value);
              checkDaysTemplate('sat', value);
            }}
            initialValue={slot.days.sat}
          />
          <DayChipInput
            label="D"
            source={`day.sun${slot.id}`}
            onChange={(value) => {
              updateSlotsDays(id, 'sun', value);
              checkDaysTemplate('sun', value);
            }}
            initialValue={slot.days.sun}
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
          initialValue={slot.outwardTimes}
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
          initialValue={slot.returnTimes}
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
  setChoice: PropTypes.func.isRequired,
  setSelectedDateTime: PropTypes.func.isRequired,
  choices: PropTypes.array.isRequired,
};

const SolidaryRegularSchedules = (props) => {
  const {
    isEditing = false,
    choices,
    initialChoice,
  } = props;
  const [choice, setChoice] = useState(choices[initialChoice]);
  const [selectedDateTime, setSelectedDateTime] = useState(null);
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

  const {
    input: { onChange: onChangeReturnDateTime },
  } = useField('returnDatetime');

  const {
    input: { value: outwardDatetime },
  } = useField('outwardDatetime');

  const classes = useStyles();

  const [id, setId] = useState(1);
  const [loadingDays, setLoadingDays] = useState(true);
  const [loadingOwt, setLoadingOwt] = useState(true);
  const [loadingRt, setLoadingRt] = useState(true);

  const daysField = useField('days');
  const outwardsField = useField('outwardTimes');
  const returnsField = useField('returnTimes');

  const updateSlotsDays = (index, days) => {
    const newArr = [...slotsList];
    newArr[index].days = days;

    setSlotsList(newArr);
  };

  const updateSlotsOWT = (index, owt) => {
    const newArr = [...slotsList];
    newArr[index].outwardTimes = owt;

    setSlotsList(newArr);
  };

  const updateSlotsRT = (index, rt) => {
    const newArr = [...slotsList];
    newArr[index].returnTimes = rt;

    setSlotsList(newArr);
  };

  useEffect(() => {
    if (loadingDays) {
      const initialDays = { ...daysField.meta.initial };
      console.log('[EDITION] Set Initial Days From Given Values: ', initialDays);
      setDays(initialDays);
      setLoadingDays(false);
    }
  }, [daysField.meta.initial, loadingDays]);

  useEffect(() => {
    if (loadingOwt) {
      const initialOwt = { ...outwardsField.meta.initial };
      console.log('[EDITION] Set Initial Outward Times From Given Values: ', initialOwt);
      setOutwardTimes(initialOwt);
      setLoadingOwt(false);
    }
  }, [outwardsField.meta.initial, loadingOwt]);

  useEffect(() => {
    if (loadingRt) {
      const initialRt = { ...returnsField.meta.initial };
      console.log('[EDITION] RTF META: ', returnsField.meta);
      console.log('[EDITION] Set Initial Return Times From Given Values: ', initialRt);
      setReturnTimes(initialRt);
      setLoadingRt(false);
    }
  }, [returnsField.meta.initial, loadingRt]);

  useEffect(() => {
    if (!loadingRt && !loadingDays && !loadingOwt) {
      console.log('[EDITION]RETURN', returnTimes);
      let newSlots = [];

      for (const [key, value] of Object.entries(days)) {
        if (value) {
          let found = false;
          for (let i = 0; i < newSlots.length; i += 1) {
            if (outwardTimes[key] === newSlots[i].outwardTimes
              && returnTimes[key] === newSlots[i].returnTimes) {
              newSlots[i].days[key] = value;
              found = true;
            }
          }
          if (!found) {
            newSlots.push({
              id: `${newSlots.length}:${outwardTimes[key]}`,
              days: { mon: false, tue: false, wed: false, thu: false, fri: false, sat: false, sun: false, [key]: value },
              outwardTimes: outwardTimes[key],
              returnTimes: returnTimes[key],
            })
          }
        }
      }
      console.log('[EDITION] RESULTAT FINALS SLOTS: ', newSlots);
      setSlotsList(newSlots);
    }
  }, [loadingRt, loadingDays, loadingOwt])

  useEffect(() => {
    console.log('Days template:', days);
  }, [days]);

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

  useEffect(() => {
    if (choice.returnDatetime) {
      onChangeReturnDateTime(choice.returnDatetime({ outwardDatetime, selectedDateTime }));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [choice, selectedDateTime && selectedDateTime.toString()]);

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

  console.log(`[EDITION]Is Edition: ${isEditing} LoadOwt: ${loadingOwt} LoadDays: ${loadingDays} LoadRt: ${loadingRt}`)
  if (isEditing && (loadingOwt || loadingDays || loadingRt)) return <p>loading...</p>;

  return (
    <>
      <div className={classes.none}>
        <DayChipInput source="days.mon" label="L" forcedValue={days.mon}  />
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
            <Card raised className={classes.card} key={`${slot.id}`}>
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
                setChoice={setChoice}
                setSelectedDateTime={setSelectedDateTime}
                choices={choices}
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

SolidaryRegularSchedules.propTypes = {
  choices: PropTypes.array.isRequired,
  initialChoice: PropTypes.number.isRequired,
  isEditing: PropTypes.bool,
};

SolidaryRegularSchedules.defaultProps = {
  isEditing: false,
};
export default SolidaryRegularSchedules;
