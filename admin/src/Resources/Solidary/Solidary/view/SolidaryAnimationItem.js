import React from 'react';
import PropTypes from 'prop-types';

import { Avatar, ListItem, ListItemAvatar, ListItemText } from '@material-ui/core';

const SolidaryAnimationItem = ({ item }) => (
  <ListItem>
    <ListItemAvatar>
      <Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg" />
    </ListItemAvatar>
    <ListItemText
      primary={item.author || 'Solenne Ayzel'}
      secondary={new Date(item.updatedDate).toLocaleString()}
    />
    <ListItemText
      primary={item.actionName || "Contact d'un conducteur par mail"}
      secondary={item.related || 'Covoitureur : Umberto Picaldi'}
    />
  </ListItem>
);

SolidaryAnimationItem.propTypes = {
  item: PropTypes.object.isRequired,
};
export default SolidaryAnimationItem;
