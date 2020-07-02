import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import Downshift from 'downshift';
import { withStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import MenuItem from '@material-ui/core/MenuItem';
import { fetchUtils, FormDataConsumer, useInput } from 'react-admin';
import { useForm } from 'react-final-form';
import useDebounce from '../../utils/useDebounce';
import { useHistory } from 'react-router-dom';
import CloseIcon from '@material-ui/icons/Close';

const queryString = require('query-string');

const fetchSuggestions = (inputold) => {
  if (!inputold) {
    return new Promise((resolve, reject) => resolve([]));
  }

  const apiUrl = process.env.REACT_APP_API + process.env.REACT_APP_GEOSEARCH_RESOURCE;
  const parameters = {
    q: `${inputold}`,
  };
  const urlWithParameters = `${apiUrl}?${queryString.stringify(parameters)}`;
  return fetchUtils
    .fetchJson(urlWithParameters)
    .then((response) => response.json)
    .catch((error) => {
      console.error(error);
      return [];
    });
};

const GeocompleteFilter = (props) => {
  const { classes } = props;
  const source = props.source;
  const form = useForm();
  const fieldName = props.source || 'address';
  const {
    input: { name, onChange, value },
    meta: { touched, error },
  } = useInput(props);

  const [inputold, setInputOld] = useState('');
  const [initValue, setInitValue] = useState(value && JSON.parse(value).name);
  const [suggestions, setSuggestions] = useState([]);
  const debouncedInput = useDebounce(inputold, 500);

  const formState = form.getState();
  const errorMessage = props.validate(inputold);
  const errorState = !!(formState.submitFailed && errorMessage);
  const router = useHistory();

  if (router.location.search) {
    const search = router.location.search;
  }

  useEffect(() => {
    if (debouncedInput) {
      fetchSuggestions(debouncedInput).then((results) => {
        setSuggestions(
          results
            .filter((element) => element && element.displayLabel && element.displayLabel.length > 0)
            .slice(0, 10)
        );
      });
    } else {
      setSuggestions([]);
    }
  }, [debouncedInput]);

  const isSelected = (selectedItem, label) => (selectedItem || '').indexOf(label) > -1;

  return (
    <FormDataConsumer>
      {({ dispatch, ...rest }) => (
        <div className={classes.root}>
          <Downshift
            onInputValueChange={(inputValue) => setInputOld(inputValue ? inputValue.trim() : '')}
            onSelect={(selectedItem, stateAndHelpers) => {
              const address = suggestions.find((element) => element.displayLabel === selectedItem);
              if (address) {
                const addressParam = {
                  name: address.displayLabel.join(),
                  lgt: address.longitude,
                  lat: address.latitude,
                };
                onChange(JSON.stringify(addressParam));
              }
            }}
            initialInputValue={initValue}
          >
            {({
              getInputProps,
              getItemProps,
              isOpen,
              selectedItem,
              highlightedIndex,
              clearSelection,
            }) => (
              <div className={classes.container}>
                <TextField
                  name={name}
                  label={props.label}
                  error={!!(touched && error)}
                  helperText={touched && error}
                  style={{ minWidth: '100vh', marginBottom: '15px' }}
                  InputProps={{
                    ...getInputProps({
                      placeholder: 'Entrer une adresse',
                    }),
                  }}
                />
                <CloseIcon
                  style={{ position: 'absolute', bottom: '18px', right: '0px', cursor: 'pointer' }}
                  onClick={() => console.info(onChange())}
                />

                {isOpen ? (
                  <Paper className={classes.paper} square>
                    {suggestions.map((suggestion, index) => (
                      <MenuItem
                        {...getItemProps({
                          item: suggestion.displayLabel,
                        })}
                        key={suggestion.displayLabel}
                        selected={highlightedIndex === index}
                        component="div"
                        style={{
                          fontWeight: isSelected(selectedItem, suggestion.displayLabel) ? 500 : 400,
                        }}
                      >
                        {suggestion.displayLabel.join(' ')}
                      </MenuItem>
                    ))}
                  </Paper>
                ) : null}
              </div>
            )}
          </Downshift>
        </div>
      )}
    </FormDataConsumer>
  );
};

GeocompleteFilter.propTypes = {
  classes: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
  label: PropTypes.string,
  validate: PropTypes.func,
};

GeocompleteFilter.defaultProps = {
  label: '',
  validate: () => '',
};

const styles = (theme) => ({
  root: {
    flexGrow: 1,
  },
  container: {
    flexGrow: 1,
    position: 'relative',
  },
  paper: {
    position: 'absolute',
    zIndex: 9999,
    marginTop: theme.spacing(1),
    left: 0,
    right: 0,
  },
  chip: {
    margin: `${theme.spacing(0.5)}px ${theme.spacing(0.25)}px`,
  },
  inputRoot: {
    flexWrap: 'wrap',
  },
  divider: {
    height: theme.spacing(2),
  },
  input: {
    // width: '50%',   // Change this to style the autocomplete component
    flexGrow: 1,
  },
});

export default withStyles(styles)(GeocompleteFilter);
