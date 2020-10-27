import React from 'react';
import PropTypes from 'prop-types';
import { Grid } from '@material-ui/core';

export const NeedsAndStructure = ({ record }) => {
  const { needs, solidaryUserStructure, operator, days, outwardTimes, returnTimes } = record;

  const createSlots = () => {
    const newSlots = [];

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
    console.log('[EDITION] RESULTAT FINALS SLOTS: ', newSlots);
    return newSlots;
  };

  return (
    <>
      <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
        <Grid item xs={3}>
          <b>Autres besoins :</b>
        </Grid>
        <Grid item xs={9}>
          {needs && needs.length ? needs.map((n) => n.label).join(' ') : 'Aucun'}
        </Grid>
      </Grid>

      <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
        <Grid item md={3} xs={6}>
          <b>Structure accompagnante&nbsp;:</b>
        </Grid>
        <Grid item md={3} xs={6}>
          {solidaryUserStructure.structure && solidaryUserStructure.structure.name}
        </Grid>
        <Grid item md={3} xs={6}>
          <b>Opérateur ayant enregistré la demande&nbsp;:</b>
        </Grid>
        <Grid item md={3} xs={6}>
          {operator ? `${operator.givenName} ${operator.familyName}` : 'Non renseigné.'}
        </Grid>
      </Grid>
    </>
  );
};

NeedsAndStructure.propTypes = {
  record: PropTypes.object.isRequired,
};
