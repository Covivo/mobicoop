import React from 'react';
import PropTypes from 'prop-types';
import { Avatar, List, ListItem, ListItemAvatar, ListItemText, Grid } from '@material-ui/core';

import { formatPhone } from '../../SolidaryUserBeneficiary/Fields/PhoneField';
import { SolidaryContactDropDown } from './SolidaryContactDropDown';

/*
[
    {
        "Type": "transporter",
        "FamilyName": "Solidaire",
        "GivenName": "Jean-Michel",
        "Telephone": "0604050802",
        "UserId": 13
    }
]
*/

const SolidarySolutions = ({ solidaryId, solutions }) => {
  if (solutions && solutions.length) {
    return (
      <List>
        {solutions.map((s) => (
          <ListItem>
            <ListItemAvatar>
              <Avatar alt={s.GivenName || 'Inconnu'} src="/static/images/avatar/1.jpg" />
            </ListItemAvatar>
            <Grid container>
              <Grid item xs={4}>
                <ListItemText
                  primary={s.GivenName ? `${s.GivenName} ${s.FamilyName}` : 'Inconnu'}
                  secondary={s.Type || ''}
                />
              </Grid>
              <Grid item xs={4}>
                <ListItemText
                  primary={s.Telephone ? formatPhone(s.Telephone) : 'Téléphone non renseigné'}
                />{' '}
              </Grid>
              <Grid item xs={4}>
                <ListItemText>
                  <SolidaryContactDropDown
                    solidarySolutionId={s.id}
                    solidaryId={solidaryId}
                    label="Contacter conducteur"
                  />
                </ListItemText>
              </Grid>
            </Grid>
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
