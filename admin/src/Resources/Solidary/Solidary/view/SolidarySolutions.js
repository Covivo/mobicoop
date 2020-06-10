import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';
import { Card, Avatar, List, ListItem, ListItemAvatar, ListItemText } from '@material-ui/core';
import DropDownButton from '../../../../components/button/DropDownButton';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
}));

/*

"solutions": [
        {
            "Type": "transporter",
            "FamilyName": "Solidaire",
            "GivenName": "Jean-Michel",
            "Telephone": "0604050802",
            "UserId": 13
        }
    ]
*/

const contactOptions = ['SMS', 'Email', 'Téléphone'];

const SolidarySolutions = ({ solutions }) => {
  const classes = useStyles();

  console.log('data :', solutions);

  if (solutions && solutions.length) {
    return (
      <List>
        {solutions.map((s) => (
          <ListItem>
            <ListItemAvatar>
              <Avatar alt={s.GivenName || 'Inconnu'} src="/static/images/avatar/1.jpg" />
            </ListItemAvatar>
            <ListItemText
              primary={s.GivenName ? `${s.GivenName} ${s.FamilyName}` : 'Inconnu'}
              secondary={s.Type || ''}
            />
            <ListItemText primary={s.Telephone || 'Téléphone non renseigné'} />
            <ListItemText>
              <DropDownButton label="Contacter conducteur" options={contactOptions} />
            </ListItemText>
          </ListItem>
        ))}
      </List>
    );
  }

  return <List>Pas encore de conducteurs potentiels pour cette demande</List>;
};

SolidarySolutions.propTypes = {
  solutions: PropTypes.object.isRequired,
};

export default SolidarySolutions;
