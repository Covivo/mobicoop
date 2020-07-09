import React, { useState } from 'react';
import { DateInput } from 'react-admin-date-inputs';
import { Dialog, DialogTitle, makeStyles, Grid, Button, Chip } from '@material-ui/core';
import { fr } from 'date-fns/locale';

import {
  getHours,
  getMinutes,
  addWeeks,
  addMonths,
  format,
  eachDayOfInterval,
  isMonday,
  isTuesday,
  isWednesday,
  isThursday,
  isFriday,
  isSaturday,
  isSunday,
} from 'date-fns';

import {
  Loading,
  useTranslate,
  FormWithRedirect,
  BooleanInput,
  TextInput,
  FormDataConsumer,
  useMutation,
  useRefresh,
} from 'react-admin';

import { useSolidary } from '../hooks/useSolidary';
import { solidaryLabelRenderer } from '../../../../utils/renderers';
import { SliderInput } from '../input/SliderInput';
import { SolidaryJourney } from './SolidaryJourney';

const useStyles = makeStyles({
  loading: { height: '50vh' },
  infos: { padding: 20 },
  form: { padding: 20 },
});

const isDayFromString = (day) => {
  const checker = {
    mon: isMonday,
    tue: isTuesday,
    wed: isWednesday,
    thu: isThursday,
    fri: isFriday,
    sat: isSaturday,
    sun: isSunday,
  }[day];

  return checker || (() => false);
};

const getDayInterval = ({ start, end }, day) => {
  const days = eachDayOfInterval({ start, end }).filter(isDayFromString(day));
  const { 0: first, [days.length - 1]: last } = days;
  return { first, last: last === first ? undefined : last };
};

const hour = (date) => `${getHours(date)}:${getMinutes(date)}`;
const collectSchedule = (schedule, mode = 'outward') => {
  const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

  const scheduleMap = days
    .filter((day) => schedule[day])
    .reduce((acc, day) => {
      const time = schedule[`${day}${mode === 'outward' ? 'OutwardTime' : 'ReturnTime'}`];
      if (!time) {
        return acc;
      }

      if (!acc[time]) {
        acc[time] = [];
      }

      acc[time].push(day);
      return acc;
    }, {});

  return Object.keys(scheduleMap).map((time) => ({
    [mode === 'outward' ? 'outwardTime' : 'returnTime']: hour(new Date(time)),
    ...days.reduce(
      (acc, day) => ({
        ...acc,
        [day]: scheduleMap[time].includes(day) ? 1 : 0,
      }),
      {}
    ),
  }));
};

const ScheduleGrid = ({ schedule, interval, mode = 'outward' }) => {
  const translate = useTranslate();

  const sourceKey = mode === 'outward' ? 'outwardSchedule' : 'returnSchedule';
  const sourceTimeKey = mode === 'outward' ? 'outwardTime' : 'returnTime';

  const countRows = schedule.reduce(
    (acc, row) =>
      acc +
      Object.keys(row)
        .filter((k) => k !== sourceTimeKey)
        .reduce((acc, k) => acc + row[k], 0),
    0
  );

  if (!countRows) {
    return <span>Pas de retours disponibles</span>;
  }

  return (
    <>
      {schedule.map((scheduleEntry, index) =>
        Object.keys(scheduleEntry).map((entryKey) => {
          if (entryKey === sourceTimeKey) {
            return (
              <TextInput
                key={`${index}-${entryKey}`}
                style={{ display: 'none' }}
                initialValue={scheduleEntry[sourceTimeKey]}
                source={`${sourceKey}[${index}].${sourceTimeKey}`}
              />
            );
          }

          const { first: firstDay, last: lastDay } = getDayInterval(interval, entryKey);

          const dayInterval = [
            firstDay && format(firstDay, 'eee dd LLL', { locale: fr }),
            lastDay && format(lastDay, 'eee dd LLL', { locale: fr }),
          ]
            .filter((x) => x)
            .join(' - ');

          return (
            <Grid
              container
              alignContent="center"
              alignItems="center"
              key={`${index}-${entryKey}`}
              style={{
                display: scheduleEntry[entryKey] === 0 ? 'none' : 'flex',
              }}
            >
              <Grid item>
                {translate(`custom.days.${entryKey}`)} {scheduleEntry[sourceTimeKey]}
              </Grid>
              <Grid item style={{ width: 100, margin: '0 20px' }}>
                <BooleanInput
                  label=""
                  helperText={false}
                  initialValue={scheduleEntry[entryKey]}
                  source={`${sourceKey}[${index}].${entryKey}`}
                />
              </Grid>
              <Grid item>{dayInterval}</Grid>
            </Grid>
          );
        })
      )}
    </>
  );
};

const addDaysSteps = [
  { label: '1 semaine', transformer: (date) => addWeeks(date, 1) },
  { label: '1 mois', transformer: (date) => addMonths(date, 1) },
  { label: '3 mois', transformer: (date) => addMonths(date, 3) },
  { label: "Jusqu'au", transformer: null },
];

const defaultValues = {
  outwardDate: new Date(),
  outwardLimitDate: addWeeks(new Date(), 1),
  outwardSchedule: [],
  returnSchedule: [],
};

export const SolidaryFormalResponseModal = ({ solidaryId, solidarySolutionId, onClose }) => {
  const classes = useStyles();
  const translate = useTranslate();
  const [step, setStep] = useState(0);
  const refresh = useRefresh();

  const [send, { loading: loadingSubmit }] = useMutation(
    {},
    {
      onSuccess: () => {
        onClose();
        refresh();
      },
    }
  );

  const { solidary, loading } = useSolidary(`/solidaries/${solidaryId}`);

  const handleSubmit = (values) => {
    const outwardDate = format(values.outwardDate, "yyyy-MM-dd'T'HH:mm:ssxxx");
    const outwardLimitDate = format(values.outwardLimitDate, "yyyy-MM-dd'T'HH:mm:ssxxx");

    const solidaryFormalRequest = {
      outwardSchedule: values.outwardSchedule || [],
      returnSchedule: values.returnSchedule || [],
      solidarySolution: `/solidary_solutions/${solidarySolutionId}`,
      outwardDate,
      outwardLimitDate,
      returnDate: outwardDate, // return value = outward one
      returnLimitDate: outwardLimitDate, // return value = outward one},
    };

    return send({
      type: 'create',
      resource: 'solidary_formal_requests',
      payload: {
        data: solidaryFormalRequest,
      },
    });
  };

  const ask =
    solidary && solidary.asksList.find((i) => i.solidarySolutionId === solidarySolutionId);

  const outwardScheduleDays = ask ? collectSchedule(ask.schedule, 'outward') : [];
  const returnScheduleDays = ask ? collectSchedule(ask.schedule, 'return') : [];

  return (
    <Dialog fullWidth maxWidth="md" open onClose={onClose}>
      {loading ? (
        <Loading className={classes.loading} />
      ) : solidary ? (
        <>
          <DialogTitle>
            {ask && ask.driver
              ? translate('custom.solidary.askFormalResponseWith', { username: ask.driver })
              : translate('custom.solidary.askFormalResponse')}
          </DialogTitle>
          <Grid container>
            <Grid item xs={8}>
              <div className={classes.form}>
                <FormWithRedirect
                  resource="solidary_formal_requests"
                  save={handleSubmit}
                  initialValues={defaultValues}
                  render={({ handleSubmitWithRedirect, saving, form }) => (
                    <div>
                      {step === 0 && (
                        <FormDataConsumer>
                          {({ formData }) => (
                            <>
                              <Grid container justify="space-between">
                                <Grid item>
                                  <DateInput source="outwardDate" style={{ width: '100px' }} />
                                </Grid>
                                <Grid item style={{ width: '300px' }}>
                                  <SliderInput
                                    source="dayStep"
                                    initialValue={0}
                                    onChange={(stepIndex) => {
                                      const { transformer } = addDaysSteps[stepIndex];
                                      if (!transformer || !formData.outwardDate) {
                                        return null;
                                      }

                                      form.change(
                                        'outwardLimitDate',
                                        transformer(formData.outwardDate)
                                      );
                                    }}
                                    choices={addDaysSteps.map((step) => step.label)}
                                  />
                                </Grid>
                                <Grid item>
                                  <DateInput
                                    disabled={formData.dayStep !== 3}
                                    source="outwardLimitDate"
                                    style={{ width: '100px' }}
                                  />
                                </Grid>
                              </Grid>
                              <Grid container alignItems="center" style={{ margin: '20px 0' }}>
                                <Grid item>
                                  <Chip label="Aller" />
                                  &nbsp;
                                </Grid>
                                <Grid item>
                                  <SolidaryJourney emptyText="" solidary={solidary.id} />
                                </Grid>
                              </Grid>
                              <ScheduleGrid
                                interval={{
                                  start: formData.outwardDate,
                                  end: formData.outwardLimitDate,
                                }}
                                schedule={outwardScheduleDays}
                                mode="outward"
                              />
                            </>
                          )}
                        </FormDataConsumer>
                      )}
                      {step === 1 && (
                        <FormDataConsumer>
                          {({ formData }) => (
                            <>
                              <Grid container alignItems="center" style={{ margin: '20px 0' }}>
                                <Grid item>
                                  <Chip label="Retour" />
                                  &nbsp;
                                </Grid>
                                <Grid item>
                                  <SolidaryJourney reverse emptyText="" solidary={solidary.id} />
                                </Grid>
                              </Grid>
                              <ScheduleGrid
                                interval={{
                                  start: formData.outwardDate,
                                  end: formData.outwardLimitDate,
                                }}
                                schedule={returnScheduleDays}
                                mode="return"
                              />
                            </>
                          )}
                        </FormDataConsumer>
                      )}
                      <Grid container justify="space-between" style={{ paddingTop: 20 }}>
                        <Grid item>
                          <Button
                            variant="contained"
                            color="primary"
                            disabled={step === 0}
                            onClick={() => setStep(0)}
                          >
                            Précédent
                          </Button>
                        </Grid>
                        <Grid item>
                          {step === 0 && (
                            <Button
                              disabled={loadingSubmit}
                              variant="contained"
                              color="primary"
                              onClick={() => setStep(1)}
                            >
                              Choisir les retours
                            </Button>
                          )}
                          {step === 1 && (
                            <Button
                              variant="contained"
                              color="primary"
                              disabled={saving || loadingSubmit}
                              onClick={handleSubmitWithRedirect}
                            >
                              Solliciter une réponse formelle
                            </Button>
                          )}
                        </Grid>
                      </Grid>
                    </div>
                  )}
                />
              </div>
            </Grid>
            <Grid item xs={4}>
              <div className={classes.infos}>
                {solidary.solidaryUser && solidary.solidaryUser.user && (
                  <>
                    <p>{`${translate('custom.solidary.onBehalfOf')}: `}</p>
                    <p>
                      <strong>{`${solidary.solidaryUser.user.givenName} ${solidary.solidaryUser.user.familyName}`}</strong>
                    </p>
                  </>
                )}
                {solidary.displayLabel && (
                  <>
                    <p>{`${translate('custom.solidary.associatedAsk')}:`}</p>
                    <p>
                      <strong>
                        {solidaryLabelRenderer({
                          record: solidary,
                        })}
                      </strong>
                    </p>
                  </>
                )}
              </div>
            </Grid>
          </Grid>
        </>
      ) : null}
    </Dialog>
  );
};
