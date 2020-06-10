import React from 'react';
import PropTypes from 'prop-types';

import { Avatar, ListItem, ListItemAvatar, ListItemText } from '@material-ui/core';

const SolidaryAnimationItem = ({ item }) => (
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
    <ListItemText
      primary={item.author ? `${item.author.givenName} ${item.author.familyName}` : 'Inconnu'}
      secondary={new Date(item.updatedDate).toLocaleString()}
    />
    <ListItemText
      primary={item.actionName || "Contact d'un conducteur par mail"}
      secondary={
        item.user && item.user.familyName ? `${item.user.givenName} ${item.user.familyName}` : ''
      }
    />
  </ListItem>
);

SolidaryAnimationItem.propTypes = {
  item: PropTypes.object.isRequired,
};
export default SolidaryAnimationItem;
