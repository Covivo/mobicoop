import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';

import { Button } from '@material-ui/core';

const CreateRelatedActionButton = ({ record }) => (
  <Button
    variant="contained"
    color="primary"
    component={Link}
    to={{
      pathname: '/solidary_animations/create',
      state: {
        record: {
          solidary: record.id,
          user: record.solidaryUser.user['@id'],
          actionName: 'solidary_update_progress_manually',
        },
      },
    }}
  >
    Nouvelle action
  </Button>
);

CreateRelatedActionButton.propTypes = {
  record: PropTypes.object.isRequired,
};

export default CreateRelatedActionButton;
