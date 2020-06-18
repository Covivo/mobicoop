import React from 'react';
import ArrowDropDownIcon from '@material-ui/icons/ArrowDropDown';

import {
  Grid,
  Button,
  ButtonGroup,
  ClickAwayListener,
  Paper,
  Popover,
  MenuItem,
  MenuList,
} from '@material-ui/core';

const DropDownButton = ({ label, options, onSelect, size = 'medium', variant = 'text' }) => {
  const [open, setOpen] = React.useState(false);
  const anchorRef = React.useRef(null);

  const handleMenuItemClick = (key) => {
    onSelect(options[key], key);
    setOpen(false);
  };

  const handleToggle = () => {
    setOpen((prevOpen) => !prevOpen);
  };

  const handleClose = (event) => {
    if (anchorRef.current && anchorRef.current.contains(event.target)) {
      return;
    }
    setOpen(false);
  };

  return (
    <Grid container direction="column" alignItems="center">
      <Grid item xs={12}>
        <ButtonGroup variant="contained" color="primary" ref={anchorRef} aria-label="split button">
          <Button
            color="primary"
            size={size}
            aria-controls={open ? 'split-button-menu' : undefined}
            aria-expanded={open ? 'true' : undefined}
            aria-label="select merge strategy"
            aria-haspopup="menu"
            onClick={handleToggle}
            variant={variant}
          >
            <ArrowDropDownIcon />
            {label}
          </Button>
        </ButtonGroup>
        <Popover
          open={open}
          anchorEl={anchorRef.current}
          anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
          transformOrigin={{ vertical: 'top', horizontal: 'center' }}
        >
          <Paper style={{ backgroundColor: 'white', zIndex: '10' }}>
            <ClickAwayListener onClickAway={handleClose}>
              <MenuList id="split-button-menu">
                {Object.keys(options).map((key) => (
                  <MenuItem key={options[key]} onClick={() => handleMenuItemClick(key)}>
                    {options[key]}
                  </MenuItem>
                ))}
              </MenuList>
            </ClickAwayListener>
          </Paper>
        </Popover>
      </Grid>
    </Grid>
  );
};

export default DropDownButton;
