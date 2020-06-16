import React from 'react';
import PropTypes from 'prop-types';
import { Grid } from '@material-ui/core';

export const NeedsAndStructure = ({ record }) => {
  const { needs, solidaryUserStructure, operator } = record;

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
          {operator ? `${operator.givenName} ${operator.familyName}&nbsp;` : 'Non renseigné.'}
        </Grid>
      </Grid>
    </>
  );
};

NeedsAndStructure.propTypes = {
  record: PropTypes.object.isRequired,
};
