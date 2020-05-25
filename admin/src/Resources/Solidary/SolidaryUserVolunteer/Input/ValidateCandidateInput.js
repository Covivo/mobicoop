import React, { useState } from 'react';
import { useInput, useTranslate } from 'react-admin';
import { Button, Menu, MenuItem } from '@material-ui/core';

export const ValidateCandidateInput = (props) => {
  const { input } = useInput(props);
  const translate = useTranslate();

  const [anchorEl, setAnchorEl] = useState(null);

  const handleOpenMenu = (event) => setAnchorEl(event.currentTarget);
  const handleCloseMenu = () => setAnchorEl(null);

  const isValidated = !!input.value;

  const handleChoice = () => {
    input.onChange(!isValidated);
    handleCloseMenu();
  };

  return (
    <>
      <Button
        aria-controls="simple-menu"
        aria-haspopup="true"
        variant="contained"
        color="secondary"
        onClick={handleOpenMenu}
      >
        {isValidated
          ? translate('custom.solidary_volunteers.input.validatedCandidate')
          : translate('custom.solidary_volunteers.input.rejectedCandidate')}
      </Button>
      <Menu
        id="simple-menu"
        anchorEl={anchorEl}
        keepMounted
        open={Boolean(anchorEl)}
        onClose={handleCloseMenu}
      >
        {isValidated ? (
          <MenuItem onClick={handleChoice}>
            {translate('custom.solidary_volunteers.input.rejectCandidate')}
          </MenuItem>
        ) : (
          <MenuItem onClick={handleChoice}>
            {translate('custom.solidary_volunteers.input.acceptCandidate')}
          </MenuItem>
        )}
      </Menu>
    </>
  );
};
