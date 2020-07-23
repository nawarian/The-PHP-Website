import Fuse from 'fuse.js';
import moment from 'moment';
import { fetch } from 'whatwg-fetch';

const fillSearchResults = (results) => {
    const resultsElement = document.querySelector('.search-results');

    const resultHtml = results.map(({ item: { createdAt, title, description, url, imageUrl, imageAlt } }) => `
        <div class="card">
          <a href="${url}">
            <div
              class="card__image"
              style="background-image: url('${imageUrl}')"
              alt="${imageAlt}"
            ></div>
    
            <div class="card__content">
              <h3 class="card__content-heading">${title}</h3>
              <p>${description}</p>
              <time>${moment(createdAt * 1000).format('YYYY/MM/DD')}</time>
            </div>
          </a>
        </div>
    `).join(' ');

    resultsElement.innerHTML = resultHtml;
};

const registerSearchListeners = (fuse) => {
    const lang = document.querySelector('html').getAttribute('lang');

    const searchInput = document.querySelector('#search-box');
    let timer = null;
    searchInput.onkeyup = ({ target: { value: searchTerm } }) => {
        if (timer !== null) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            const results = fuse
                .search(searchTerm)
                .filter(elm => elm.item.lang === lang)
                .filter(elm => elm.item.imageUrl);

            fillSearchResults(results);
        }, 200);
    };
};

fetch('/search-index.json')
    .then(async (response) => {
        const index = await response.json();

        const fuse = new Fuse(index, {
            minMatchCharLength: 5,
            keys: [
                {
                    name: 'title',
                    weight: 2,
                },
                {
                    name: 'description',
                    weight: 2,
                },
                {
                    name: 'tags',
                    weight: 1,
                }
            ],
        });

        registerSearchListeners(fuse);
    });
