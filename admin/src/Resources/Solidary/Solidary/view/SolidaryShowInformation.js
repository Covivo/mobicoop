import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';
import RoomIcon from '@material-ui/icons/Room';
import { useHistory } from 'react-router-dom';

import {
  Card,
  Grid,
  Avatar,
  LinearProgress,
  Stepper,
  Step,
  StepLabel,
  Divider,
} from '@material-ui/core';

import DropDownButton from '../../../../components/button/DropDownButton';
import DayChip from './DayChip';
import SolidaryPlace from './SolidaryPlace';
import SolidarySchedule from './SolidarySchedule';
import SolidaryAnimation from './SolidaryAnimation';
import SolidarySolutions from './SolidarySolutions';

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

const SMS_CONTACT_OPTION = 'SMS';
const EMAIL_CONTACT_OPTION = 'Email';
const PHONE_CONTACT_OPTION = 'Téléphone';

const contactOptions = [SMS_CONTACT_OPTION, EMAIL_CONTACT_OPTION, PHONE_CONTACT_OPTION];
const driverSearchOptions = [
  {
    label: 'Rechercher aller covoiturage',
    filter: (solidaryId) => ({ way: 'outward', type: 'carpool', solidary: solidaryId }),
  },
  {
    label: 'Rechercher retour covoiturage',
    filter: (solidaryId) => ({ way: 'return', type: 'carpool', solidary: solidaryId }),
  },
  {
    label: 'Rechercher bénévole aller',
    filter: (solidaryId) => ({ way: 'outward', type: 'transport', solidary: solidaryId }),
  },
  {
    label: 'Rechercher bénévole retour',
    filter: (solidaryId) => ({ way: 'return', type: 'transport', solidary: solidaryId }),
  },
];
const SolidaryShowInformation = ({ record }) => {
  const classes = useStyles();
  const history = useHistory();

  console.log('record:', record);
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

  const user = solidaryUser.user || {};
  console.log('User : ', user);

  const handleContactChoice = (choice, index) => {
    console.log(`@TODO: handling handleContactChoice ${choice}`);
  };

  const handleDriverSearch = (choice, index) => {
    const url = `/solidary_searches?filter=${encodeURIComponent(
      JSON.stringify(driverSearchOptions[index].filter(id))
    )}`;

    history.push(url);
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
              <Grid item>{`Avancement : ${progression || 0}% `}</Grid>
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

        <Divider light className={classes.divider} />
        <Grid container direction="row" justify="center" alignItems="center" spacing={2}>
          <Grid item lg={8} md={12} className={classes.path}>
            <Stepper>
              <Step active key={1}>
                <StepLabel icon={<RoomIcon />}>
                  <SolidaryPlace place={origin} />
                </StepLabel>
              </Step>
              <Step active key={1}>
                <StepLabel icon={<RoomIcon />}>
                  <SolidaryPlace place={destination} />
                </StepLabel>
              </Step>
            </Stepper>
          </Grid>
        </Grid>

        <Divider light className={classes.divider} />

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
            <b>Structure accompagnante :</b>
          </Grid>
          <Grid item md={3} xs={6}>
            {solidaryUserStructure.structure && solidaryUserStructure.structure.name}
          </Grid>
          <Grid item md={3} xs={6}>
            <b>Opérateur ayant enregistré la demande :</b>
          </Grid>
          <Grid item md={3} xs={6}>
            {operator ? `${operator.givenName} ${operator.familyName}` : 'Non renseigné.'}
          </Grid>
        </Grid>
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

        <SolidarySolutions solutions={solutions} />
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
