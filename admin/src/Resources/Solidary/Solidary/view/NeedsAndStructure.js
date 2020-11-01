import React from 'react';
import PropTypes from 'prop-types';
import { Grid } from '@material-ui/core';
import DayChip from './DayChip';

export const NeedsAndStructure = ({ record }) => {
  const { needs, solidaryUserStructure, operator, days, outwardTimes, returnTimes } = record;

  const createSlots = () => {
    const newSlots = [];
    if (days && outwardTimes && returnTimes) {
      for (const [key, value] of Object.entries(days)) {
        if (value) {
          let found = false;
          for (let i = 0; i < newSlots.length; i += 1) {
            if (
              outwardTimes[key] === newSlots[i].outwardTimes &&
              returnTimes[key] === newSlots[i].returnTimes
            ) {
              newSlots[i].days[key] = value;
              found = true;
            }
          }
          if (!found) {
            newSlots.push({
              id: newSlots.length,
              days: {
                mon: false,
                tue: false,
                wed: false,
                thu: false,
                fri: false,
                sat: false,
                sun: false,
                [key]: value,
              },
              outwardTimes: outwardTimes[key],
              returnTimes: returnTimes[key],
            });
          }
        }
      }
    }
    return newSlots;
  };
  const slotsList = createSlots();

  return (
    <>
      <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
        <Grid item xs={3}>
          <b>Autres besoins : </b>
          {needs && needs.length ? needs.map((n) => n.label).join(' ') : 'Aucun'}
        </Grid>
      </Grid>

      <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
        <Grid item md={4} xs={6}>
          <b>Structure accompagnante&nbsp;: </b>
          {solidaryUserStructure.structure && solidaryUserStructure.structure.name}
        </Grid>
        <Grid item md={4} xs={6}>
          <b>Opérateur ayant enregistré la demande&nbsp;: </b>
          {operator ? `${operator.givenName} ${operator.familyName}` : 'Non renseigné.'}
        </Grid>
        <Grid container md={4} xs={6} spacing={2}>
          {slotsList.map((slot) => {
            return (
              <Grid item>
                <Grid item>
                  <DayChip key={`L${slot.id}`} label="L" condition={slot.days.mon} />
                  <DayChip key={`M${slot.id}`} label="M" condition={slot.days.tue} />
                  <DayChip key={`Me${slot.id}`} label="Me" condition={slot.days.wed} />
                  <DayChip key={`J${slot.id}`} label="J" condition={slot.days.thu} />
                  <DayChip key={`V${slot.id}`} label="V" condition={slot.days.fri} />
                  <DayChip key={`S${slot.id}`} label="S" condition={slot.days.sat} />
                  <DayChip key={`D${slot.id}`} label="D" condition={slot.days.sun} />
                </Grid>
                <Grid item>
                  <Grid container direction="row" justify="space-between">
                    <Grid item>
                      <b>Aller: </b>
                      {slot.outwardTimes}
                    </Grid>
                    <Grid item>
                      <b>Retour: </b>
                      {slot.returnTimes}
                    </Grid>
                  </Grid>
                </Grid>
              </Grid>
            );
          })}
        </Grid>
      </Grid>
    </>
  );
};

NeedsAndStructure.propTypes = {
  record: PropTypes.object.isRequired,
};
