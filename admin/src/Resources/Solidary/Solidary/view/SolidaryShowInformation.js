import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';
import { useHistory } from 'react-router-dom';

import { Card, Grid, Avatar, Divider } from '@material-ui/core';

import DropDownButton from '../../../../components/button/DropDownButton';
import DayChip from './DayChip';
import SolidarySchedule from './SolidarySchedule';
import SolidaryAnimation from './SolidaryAnimation';
import SolidarySolutions from './SolidarySolutions';
import { formatPhone } from '../../SolidaryUserBeneficiary/Fields/PhoneField';
import { Trip } from './Trip';
import { NeedsAndStructure } from './NeedsAndStructure';
import { SolidaryProgress } from './SolidaryProgress';
import can from '../../../../auth/permissions';

const useStyles = makeStyles((theme) => ({
  main_panel: {
    backgroundColor: 'white',
    padding: theme.spacing(2, 4, 3),
    marginTop: '2rem',
  },
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
  path: {
    width: '50%',
  },
  quarter: {
    width: '25%',
  },
  divider: {
    marginBottom: '1rem',
  },
}));

const driverSearchOptions = [
  {
    label: 'Rechercher aller covoiturage',
    target: 'solidary_searches',
    filter: (solidaryId) => ({ way: 'outward', type: 'carpool', solidary: solidaryId }),
  },
  {
    label: 'Rechercher retour covoiturage',
    target: 'solidary_searches',
    filter: (solidaryId) => ({ way: 'return', type: 'carpool', solidary: solidaryId }),
  },
  can('solidary_volunteer_list') && {
    label: 'Rechercher bénévole aller',
    target: 'solidary_volunteers',
    filter: (solidaryId) => ({ validatedCandidate: true, solidary: solidaryId }),
  },
  can('solidary_volunteer_list') && {
    label: 'Rechercher bénévole retour',
    target: 'solidary_volunteers',
    filter: (solidaryId) => ({ validatedCandidate: true, solidary: solidaryId }),
  },
].filter((x) => x);

const SolidaryShowInformation = ({ record }) => {
  const classes = useStyles();
  const history = useHistory();

  if (!record) {
    return null;
  }

  const {
    createdDate,
    updatedDate,
    id,
    originId,
    marginDuration,
    frequency,
    outwardDatetime,
    outwardDeadlineDatetime,
    returnDatetime,
    returnDeadlineDatetime,
    origin,
    destination,
    needs,
    displayLabel,
    progression,
    solidaryUserStructure,
    solidaryUser,
    operator,
    days,
    solutions,
  } = record;

  const { user } = solidaryUser || {};

  const handleDriverSearch = (choice, index) => {
    const url = `/${driverSearchOptions[index].target}?filter=${encodeURIComponent(
      JSON.stringify(driverSearchOptions[index].filter(id))
    )}`;

    history.push(url);
  };

  return (
    <>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            {user && (
              <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
                <Grid item>
                  <Avatar
                    alt="Remy Sharp"
                    src={user.avatars && user.avatars.length && user.avatars[0]}
                  />
                </Grid>
                <Grid item>
                  <h2>{`${user.givenName} ${user.familyName}`}</h2>
                </Grid>
                <Grid item>
                  <small>{user.telephone ? formatPhone(user.telephone) : 'N/A'}</small>
                </Grid>
                {/*
              // @TODO: Should we enable this since we post message from the "demandeur"
              // We can't talk to him
              <Grid item>
                <SolidaryContactDropDown solidaryId={originId} label="Contacter demandeur" />
              </Grid>
              */}
              </Grid>
            )}
          </Grid>
        </Grid>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <h1>{`Demande solidaire # ${originId}`}</h1>
          </Grid>
          <Grid item>
            <SolidaryProgress progression={progression} />
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item>
                <b>Dernière modification</b>
              </Grid>
              <Grid item>{new Date(updatedDate || createdDate).toLocaleDateString()}</Grid>
            </Grid>
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item>
                <b>Création</b>
              </Grid>
              <Grid item>{new Date(createdDate).toLocaleDateString()}</Grid>
            </Grid>
          </Grid>
        </Grid>
        <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
          <Grid item>
            <b>Objet du déplacement :</b>
          </Grid>
          <Grid item>{displayLabel}</Grid>
        </Grid>
        <Grid container spacing={2} className={classes.divider}>
          <Grid item md={4} xs={12}>
            {returnDatetime ? 'Aller <-> Retour' : 'Aller simple'}
          </Grid>
          <Grid item md={4} xs={12}>
            <SolidarySchedule
              frequency={frequency}
              outwardDatetime={outwardDatetime}
              outwardDeadlineDatetime={outwardDeadlineDatetime}
              returnDatetime={returnDatetime}
              returnDeadlineDatetime={returnDeadlineDatetime}
              monCheck={days && days.mon}
              tueCheck={days && days.tue}
              wedCheck={days && days.wed}
              thuCheck={days && days.thu}
              friCheck={days && days.fri}
              satCheck={days && days.sat}
              sunCheck={days && days.sun}
              marginDuration={marginDuration}
            />
          </Grid>
          <Grid item md={4} xs={12}>
            <center>{frequency === 1 ? <i>Trajet ponctuel</i> : <i>Trajet régulier</i>}</center>{' '}
          </Grid>
        </Grid>
        <Divider light className={classes.divider} />
        <Grid container direction="row" justify="center" alignItems="center" spacing={2}>
          <Grid item lg={8} md={12} className={classes.path}>
            <Trip origin={origin} destination={destination} />
          </Grid>
        </Grid>
        <Divider light className={classes.divider} />
        <NeedsAndStructure record={record} />
      </Card>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <b>Conducteurs potentiels</b>
          </Grid>
          <Grid item>
            <DropDownButton
              size="small"
              label="Rechercher nouveau conducteur"
              options={driverSearchOptions.map((o) => o.label)}
              onSelect={handleDriverSearch}
            />
          </Grid>
        </Grid>
        <SolidarySolutions solidaryId={originId} solutions={solutions} />
      </Card>
      <SolidaryAnimation record={record} />
    </>
  );
};

SolidaryShowInformation.propTypes = {
  record: PropTypes.object.isRequired,
  history: PropTypes.object.isRequired,
};

export default SolidaryShowInformation;
