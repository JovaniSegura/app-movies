class MovieModal {
    constructor() {
        this.modal = document.getElementById('addMovieModal');
        this.form = document.getElementById('addMovieForm');
        this.closeBtn = this.modal.querySelector('.close-modal');
        
        this.closeBtn.addEventListener('click', () => this.close());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
    }

    open() {
        this.modal.classList.remove('hidden');
    }

    close() {
        this.modal.classList.add('hidden');
        this.form.reset();
    }
}