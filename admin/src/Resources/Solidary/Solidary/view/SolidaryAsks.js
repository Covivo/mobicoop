import React from 'react';
import PropTypes from 'prop-types';

import { makeStyles } from '@material-ui/core/styles';
import { Card, Grid, Avatar, LinearProgress } from '@material-ui/core';

import DropDownButton from '../../../../components/button/DropDownButton';
import DayChip from './DayChip';
import SolidarySchedule from './SolidarySchedule';
import SolidaryAskItem from './SolidaryAskItem';

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
  progress: {
    width: '200px',
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

const contactOptions = ['SMS', 'Email', 'Téléphone'];

const SolidaryAsks = ({ record, history }) => {
  const classes = useStyles();

  const {
    createdDate,
    updatedDate,
    originId,
    marginDuration,
    frequency,
    outwardDatetime,
    outwardDeadlineDatetime,
    returnDatetime,
    returnDeadlineDatetime,
    displayLabel,
    progression,
    solidaryUser,
    days,
    asksList,
  } = record;

  const user = solidaryUser.user || {};
  console.log('User : ', user);

  const handleContactChoice = (choice, index) => {
    console.log(`@TODO: handling handleContactChoice ${choice}`);
  };

  return (
    <>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
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
                <small>{user.telephone}</small>
              </Grid>
              <Grid item>
                <DropDownButton
                  label="Contacter demandeur"
                  options={contactOptions}
                  onSelect={handleContactChoice}
                />
              </Grid>
            </Grid>
          </Grid>
        </Grid>

        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <h1>{`Demande solidaire # ${originId}`}</h1>
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item className={classes.progress}>
                <LinearProgress variant="determinate" value={progression || 0} />
              </Grid>
              <Grid item>{`Avancement : ${progression || 0}%`}</Grid>
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
            {frequency === 1 ? (
              <i>Trajet ponctuel</i>
            ) : (
              <span>
                <i>Trajet régulier</i>
                <br />
                {[
                  { label: 'L', condition: days && days.mon },
                  { label: 'M', condition: days && days.tue },
                  { label: 'Me', condition: days && days.wed },
                  { label: 'J', condition: days && days.thu },
                  { label: 'V', condition: days && days.fri },
                  { label: 'S', condition: days && days.sat },
                  { label: 'D', condition: days && days.sun },
                ].map(({ label, condition }) => (
                  <DayChip key={label} label={label} condition={condition} />
                ))}
              </span>
            )}
          </Grid>
        </Grid>

        {asksList.map((a) => (
          <SolidaryAskItem item={a} />
        ))}
      </Card>
    </>
  );
};

SolidaryAsks.propTypes = {
  record: PropTypes.object.isRequired,
  history: PropTypes.object.isRequired,
};

export default SolidaryAsks;
