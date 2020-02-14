import React from 'react';
import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';
import ButtonGroup from '@material-ui/core/ButtonGroup';
import ArrowDropDownIcon from '@material-ui/icons/ArrowDropDown';
import ClickAwayListener from '@material-ui/core/ClickAwayListener';
import Paper from '@material-ui/core/Paper';
import Popover from '@material-ui/core/Popover';
import MenuItem from '@material-ui/core/MenuItem';
import MenuList from '@material-ui/core/MenuList';

const DropDownButton = ({label, options, setSelected, size="medium", variant='text'}) => {
    const [open, setOpen] = React.useState(false);
    const anchorRef = React.useRef(null);

    const handleMenuItemClick = (event, index) => {
        setSelected(index);
        setOpen(false);
    };

    const handleToggle = () => {
        setOpen(prevOpen => !prevOpen);
    };

    const handleClose = event => {
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
        <Popover    open={open} 
                    anchorEl={anchorRef.current} 
                    anchorOrigin={{vertical: 'bottom',horizontal: 'center',}}
                    transformOrigin={{vertical: 'top',horizontal: 'center',}}>
                <Paper style={{backgroundColor:'white', zIndex:'10'}}>
                    <ClickAwayListener onClickAway={handleClose}>
                        <MenuList id="split-button-menu">
                        {options.map((option, index) => (
                            <MenuItem
                            key={option}
                            onClick={event => handleMenuItemClick(event, index)}
                            >
                            {option}
                            </MenuItem>
                        ))}
                        </MenuList>
                    </ClickAwayListener>
                </Paper>
        </Popover>
        </Grid>
    </Grid>
    );
}

export default DropDownButton
