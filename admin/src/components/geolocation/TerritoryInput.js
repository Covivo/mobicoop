import React, { useState, useEffect } from 'react';
import Downshift from 'downshift';
import { withStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import MenuItem from '@material-ui/core/MenuItem';
import { fetchUtils, FormDataConsumer, useTranslate } from 'react-admin';
import { useForm } from 'react-final-form';
import useDebounce from '../../utils/useDebounce';

const token = localStorage.getItem('token');
const httpClient = fetchUtils.fetchJson;

const queryString = require('query-string');

const fetchSuggestions = (input) => {
  if (!input) {
    return new Promise((resolve, reject) => resolve([]));
  }

  const options = {
    headers: new global.Headers({ Accept: 'application/ld+json' }),
  };

  options.headers.set('Authorization', `Bearer ${token}`);

  const apiUrl = process.env.REACT_APP_API + process.env.REACT_APP_TERRITORY_SEARCH_RESOURCE;
  const parameters = {
    name: `${input}`,
  };

  const urlWithParameters = `${apiUrl}?${queryString.stringify(parameters)}`;

  return httpClient(`${urlWithParameters}`, {
    method: 'GET',
    headers: options.headers,
  })
    .then((response) => response.json['hydra:member'])
    .catch((error) => {
      console.error(error);
      return [];
    });
};

const TerritoryInput = (props) => {
  const form = useForm();
  const translate = useTranslate();

  const lelabel =
    props.initValue != null
      ? translate('custom.label.territory.changeTerritory')
      : translate('custom.label.territory.territory');

  const [input, setInput] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const debouncedInput = useDebounce(input, 500);

  const formState = form.getState();
  const errorMessage = props.validate ? props.validate(input) : null;
  const errorState = formState.submitFailed && errorMessage;

  useEffect(() => {
    if (debouncedInput) {
      fetchSuggestions(debouncedInput).then((results) => {
        setSuggestions(
          (results || [])
            .filter((element) => element && element.name && element.name.length > 0)
            .slice(0, 20)
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
        <div>
          <Downshift
            onInputValueChange={(inputValue, stateAndHelpers) =>
              setInput(inputValue ? inputValue.trim() : '')
            }
            onSelect={(selectedItem, stateAndHelpers) => {
              const territory = suggestions.find((element) => element.name === selectedItem);
              if (territory) {
                territory.link = '/territories/' + territory.id;
                form.change('territory', null);
                form.change('territory.id', territory.id);
                props.setTerritory(territory);
              }
            }}
          >
            {({ getInputProps, getItemProps, isOpen, selectedItem, highlightedIndex }) => (
              <div>
                <TextField
                  label={lelabel}
                  variant="filled"
                  error={errorState}
                  helperText={errorState && errorMessage}
                  InputProps={{
                    ...getInputProps({
                      placeholder: 'Entrer un territoire',
                    }),
                  }}
                  fullWidth={true}
                />

                {isOpen ? (
                  <Paper square>
                    {suggestions.map((suggestion, index) => (
                      <MenuItem
                        {...getItemProps({
                          item: suggestion.name,
                        })}
                        key={suggestion.name}
                        selected={highlightedIndex === index}
                        component="div"
                        style={{
                          fontWeight: isSelected(selectedItem, suggestion.name) ? 500 : 400,
                        }}
                      >
                        {suggestion.name}
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

TerritoryInput.propTypes = {};

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
    //width: '50%',   // Change this to style the autocomplete component
    flexGrow: 1,
  },
});

export default withStyles(styles)(TerritoryInput);
