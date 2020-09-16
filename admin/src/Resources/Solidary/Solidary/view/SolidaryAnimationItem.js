import React from 'react';
import PropTypes from 'prop-types';
import { useTranslate } from 'react-admin';
import { Avatar, ListItem, ListItemAvatar, ListItemText, Grid } from '@material-ui/core';
import { utcDateFormat } from '../../../../utils/date';

const SolidaryAnimationItem = ({ item }) => {
  const translate = useTranslate();

  return (
    <ListItem>
      <ListItemAvatar>
        <Avatar
          alt={item.author ? `${item.author.givenName} ${item.author.familyName}` : 'Inconnu'}
          src={
            item.author && item.author.avatars && item.author.avatars.length
              ? item.author.avatars[0]
              : '/static/images/avatar/1.jpg'
          }
        />
      </ListItemAvatar>
      <Grid container>
        <Grid item xs={6}>
          <ListItemText
            primary={item.author ? `${item.author.givenName} ${item.author.familyName}` : 'Inconnu'}
            secondary={utcDateFormat(item.updatedDate)}
          />
        </Grid>
        <Grid item xs={6}>
          <ListItemText
            primary={
              translate(`custom.actions.${item.actionName}`) || "Contact d'un conducteur par mail"
            }
            secondary={
              item.user && item.user.familyName
                ? `${item.user.givenName} ${item.user.familyName}`
                : ''
            }
          />
        </Grid>
      </Grid>
    </ListItem>
  );
};

SolidaryAnimationItem.propTypes = {
  item: PropTypes.object.isRequired,
};
export default SolidaryAnimationItem;
